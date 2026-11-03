<?php

namespace App\Services\Rfid;

use App\Models\AssetTag;
use App\Models\AssetTagLog;
use App\Models\RfidRawLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Turns the reads stored in rfid_raw_logs into scans (asset_tag_logs) and equipment trips.
 *
 *  - a read of a tag that is not in asset_tags ends as `unknown_tag` and creates nothing;
 *  - a read with an impossible time (in the future, or older than the retention period) ends as `invalid_time`;
 *  - a read that is already in asset_tag_logs ends as `duplicate`;
 *  - every other read is added to asset_tag_logs (`processed`) and its trip is built by MovementRecorder.
 *
 * Work is done in chunks, with a handful of set-based queries per chunk. A chunk that fails is left pending and
 * tried again on the next run; a row that keeps failing is parked as `failed` so it cannot block the others.
 */
class RawLogProcessor
{
    public const MAX_ATTEMPTS = 5;

    /** How far ahead of the server clock a reader's timestamp may be (clock drift). */
    private const FUTURE_TOLERANCE_MINUTES = 5;

    /**
     * @return array{processed: int, duplicate: int, unknown_tag: int, invalid_time: int, failed: int}
     */
    public function run(): array
    {
        $totals = [
            RfidRawLog::PROCESSED => 0,
            RfidRawLog::DUPLICATE => 0,
            RfidRawLog::UNKNOWN_TAG => 0,
            RfidRawLog::INVALID_TIME => 0,
            RfidRawLog::FAILED => 0,
        ];

        // Two runs at once would race over the same pending rows.
        $lock = Cache::lock('rfid-raw-log-processing', 600);

        if (! $lock->get()) {
            return $totals;
        }

        try {
            while (($counts = $this->processChunk()) !== null) {
                foreach ($counts as $outcome => $count) {
                    $totals[$outcome] += $count;
                }
            }
        } finally {
            $lock->release();
        }

        return $totals;
    }

    /**
     * @return array<string, int>|null null when nothing is left to do (or the chunk failed and will be retried later)
     */
    private function processChunk(): ?array
    {
        $rows = RfidRawLog::query()
            ->where('outcome', RfidRawLog::PENDING)
            ->orderBy('id')
            ->limit((int) config('rfid.process_chunk'))
            ->toBase()
            ->get(['id', 'yard_id', 'tag_id', 'status', 'detected_at', 'received_at']);

        if ($rows->isEmpty()) {
            return null;
        }

        try {
            [$counts, $stored] = DB::transaction(fn (): array => $this->classify($rows));
        } catch (Throwable $exception) {
            report($exception);
            $this->recordFailure($rows->pluck('id')->all());

            return null;
        }

        // After the scans are saved: a failure while building trips must not undo them (`rfid:build-movements` catches up).
        try {
            if ($stored->isNotEmpty()) {
                app(MovementRecorder::class)->record($stored);
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return $counts;
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return array{0: array<string, int>, 1: Collection<int, AssetTagLog>}
     */
    private function classify(Collection $rows): array
    {
        $now = now();
        $counts = [RfidRawLog::PROCESSED => 0, RfidRawLog::DUPLICATE => 0, RfidRawLog::UNKNOWN_TAG => 0, RfidRawLog::INVALID_TIME => 0];

        // lowercase => canonical tag_id (asset_tags.tag_id is the foreign key target). Compared in lowercase so the
        // match does not depend on the database collation; asset_tags is small, so no index is needed.
        $knownTags = AssetTag::query()
            ->whereIn(DB::raw('LOWER(tag_id)'), $rows->pluck('tag_id')->map(fn (string $tag): string => strtolower($tag))->unique())
            ->pluck('tag_id')
            ->mapWithKeys(fn (string $tag): array => [strtolower($tag) => $tag]);

        $latest = $now->copy()->addMinutes(self::FUTURE_TOLERANCE_MINUTES)->toDateTimeString();
        $oldest = $now->copy()->subDays((int) config('rfid.raw_log_retention_days'))->toDateTimeString();

        $unknown = [];
        $invalidTime = [];
        $candidates = [];

        foreach ($rows as $row) {
            $tag = $knownTags->get(strtolower($row->tag_id));

            match (true) {
                $tag === null => $unknown[] = $row->id,
                $row->detected_at > $latest || $row->detected_at < $oldest => $invalidTime[] = $row->id,
                default => $candidates[] = ['row' => $row, 'tag' => $tag],
            };
        }

        $this->mark($unknown, RfidRawLog::UNKNOWN_TAG, $now);
        $this->mark($invalidTime, RfidRawLog::INVALID_TIME, $now);
        $counts[RfidRawLog::UNKNOWN_TAG] = count($unknown);
        $counts[RfidRawLog::INVALID_TIME] = count($invalidTime);

        // Scans that are already stored for the yard (a read can arrive through another path too).
        $seen = $candidates === [] ? [] : AssetTagLog::query()
            ->whereIn('yard_id', collect($candidates)->pluck('row.yard_id')->unique())
            ->whereIn('tag_id', collect($candidates)->pluck('tag')->unique())
            ->whereIn('detected_at', collect($candidates)->pluck('row.detected_at')->unique())
            ->toBase()
            ->get(['yard_id', 'tag_id', 'detected_at', 'status'])
            ->mapWithKeys(fn (object $log): array => [$this->key($log->yard_id, $log->tag_id, $log->detected_at, $log->status) => true])
            ->all();

        $duplicate = [];
        $insert = [];
        $insertedIds = [];

        foreach ($candidates as ['row' => $row, 'tag' => $tag]) {
            $key = $this->key($row->yard_id, $tag, $row->detected_at, $row->status);

            if (isset($seen[$key])) {
                $duplicate[] = $row->id;

                continue;
            }

            $seen[$key] = true;
            $insertedIds[] = $row->id;
            $insert[$key] = [
                'detected_at' => $row->detected_at,
                'tag_id' => $tag,
                'yard_id' => $row->yard_id,
                'status' => $row->status,
                'received_at' => $row->received_at,
            ];
        }

        AssetTagLog::insertOrIgnore(array_values($insert));

        $this->mark($duplicate, RfidRawLog::DUPLICATE, $now);
        $this->mark($insertedIds, RfidRawLog::PROCESSED, $now);
        $counts[RfidRawLog::DUPLICATE] = count($duplicate);
        $counts[RfidRawLog::PROCESSED] = count($insertedIds);

        $stored = $insert === [] ? new Collection : AssetTagLog::query()
            ->whereIn('yard_id', collect($insert)->pluck('yard_id')->unique())
            ->whereIn('tag_id', collect($insert)->pluck('tag_id')->unique())
            ->whereIn('detected_at', collect($insert)->pluck('detected_at')->unique())
            ->get()
            ->filter(fn (AssetTagLog $log): bool => isset($insert[$this->key($log->yard_id, $log->tag_id, $log->detected_at->format('Y-m-d H:i:s'), $log->status)]))
            ->values();

        return [$counts, $stored];
    }

    /**
     * @param  list<int>  $ids
     */
    private function mark(array $ids, string $outcome, \DateTimeInterface $at): void
    {
        foreach (array_chunk($ids, 1000) as $chunk) {
            RfidRawLog::query()->whereIn('id', $chunk)->update(['outcome' => $outcome, 'processed_at' => $at]);
        }
    }

    /**
     * @param  list<int>  $ids
     */
    private function recordFailure(array $ids): void
    {
        foreach (array_chunk($ids, 1000) as $chunk) {
            RfidRawLog::query()->whereIn('id', $chunk)->increment('attempts');
            RfidRawLog::query()->whereIn('id', $chunk)->where('attempts', '>=', self::MAX_ATTEMPTS)
                ->update(['outcome' => RfidRawLog::FAILED, 'processed_at' => now()]);
        }
    }

    private function key(int|string $yard, string $tag, string $detectedAt, string $status): string
    {
        return $yard.'|'.strtolower($tag).'|'.$detectedAt.'|'.strtoupper($status);
    }
}

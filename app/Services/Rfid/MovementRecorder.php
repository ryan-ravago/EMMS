<?php

namespace App\Services\Rfid;

use App\Models\AssetMovement;
use App\Models\AssetTagLog;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Turns raw tag scans (asset_tag_logs) into equipment trips (asset_movements).
 *
 * One trip per asset, not per tag: equipment carries several tags and a reader may scan them one after
 * another, so scans of the same asset closer together than config('rfid.movement_window_seconds') belong
 * to one trip. Equipment and service vehicles are tracked; accessories are ignored.
 *
 * Each new time out / time in is handed to MovementNotifier (one email per event, not per tag) once saved.
 *
 *  - OUT: opens a trip. An older trip that never got its IN is left as it is ("missing time in").
 *  - IN: closes the latest open trip, or creates a trip with only a time in ("missing time out").
 *
 * Safe to run again over the same scans: a scan that is already part of a trip is skipped.
 */
class MovementRecorder
{
    /**
     * @param  iterable<AssetTagLog>  $logs
     * @return array{opened: int, closed: int, in_only: int, ignored: int, skipped: int}
     */
    public function record(iterable $logs): array
    {
        $logs = EloquentCollection::make($logs)->sortBy([['detected_at', 'asc'], ['id', 'asc']])->values();
        $logs->loadMissing('tag.asset.assetType');

        $result = ['opened' => 0, 'closed' => 0, 'in_only' => 0, 'ignored' => 0, 'skipped' => 0];

        /** @var list<array{0: AssetMovement, 1: 'out'|'in'}> $events */
        $events = [];

        DB::transaction(function () use ($logs, &$result, &$events): void {
            $alreadyRecorded = AssetMovement::query()
                ->whereIn('out_log_id', $logs->pluck('id'))
                ->pluck('out_log_id')
                ->merge(AssetMovement::query()->whereIn('in_log_id', $logs->pluck('id'))->pluck('in_log_id'))
                ->flip();

            foreach ($logs as $log) {
                $asset = $log->tag?->asset;

                if ($alreadyRecorded->has($log->id) || $asset === null || ! $asset->isMovementTracked()) {
                    $result['skipped']++;

                    continue;
                }

                [$outcome, $movement] = $log->status === 'OUT' ? $this->recordOut($log) : $this->recordIn($log);
                $result[$outcome]++;

                // A scan merged into a trip that is already there ('ignored') is not a new event, so it sends nothing:
                // one time out / time in of an asset is one email, however many of its tags were read.
                if ($movement !== null) {
                    $events[] = [$movement, $outcome === 'opened' ? 'out' : 'in'];
                }
            }
        });

        if ($events !== []) {
            // After the trips are committed (and never for a dry run that rolls back); a failure here changes nothing.
            DB::afterCommit(function () use ($events): void {
                try {
                    app(MovementNotifier::class)->notify($events);
                } catch (Throwable $exception) {
                    report($exception);
                }
            });
        }

        return $result;
    }

    /**
     * @return array{0: 'opened'|'ignored', 1: ?AssetMovement}
     */
    private function recordOut(AssetTagLog $log): array
    {
        $assetId = $log->tag->asset_parent_id;

        $sameTrip = AssetMovement::query()
            ->whereNull('disregarded_at')
            ->where('asset_id', $assetId)
            ->whereBetween('time_out', $this->window($log))
            ->exists();

        if ($sameTrip) {
            return ['ignored', null];
        }

        $movement = AssetMovement::query()->create([
            'asset_id' => $assetId,
            'tag_id' => $log->tag_id,
            'out_log_id' => $log->id,
            'time_out' => $log->detected_at,
            'yard_out_id' => $log->yard_id,
        ]);

        return ['opened', $movement];
    }

    /**
     * @return array{0: 'closed'|'in_only'|'ignored', 1: ?AssetMovement}
     */
    private function recordIn(AssetTagLog $log): array
    {
        $assetId = $log->tag->asset_parent_id;

        $sameTrip = AssetMovement::query()
            ->whereNull('disregarded_at')
            ->where('asset_id', $assetId)
            ->whereBetween('time_in', $this->window($log))
            ->exists();

        if ($sameTrip) {
            return ['ignored', null];
        }

        $open = AssetMovement::query()
            ->open()
            ->where('asset_id', $assetId)
            ->where('time_out', '<=', $log->detected_at)
            ->orderByDesc('time_out')
            ->first();

        if ($open !== null) {
            $open->update([
                'in_log_id' => $log->id,
                'time_in' => $log->detected_at,
                'yard_in_id' => $log->yard_id,
            ]);

            return ['closed', $open];
        }

        $movement = AssetMovement::query()->create([
            'asset_id' => $assetId,
            'tag_id' => $log->tag_id,
            'in_log_id' => $log->id,
            'time_in' => $log->detected_at,
            'yard_in_id' => $log->yard_id,
        ]);

        return ['in_only', $movement];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function window(AssetTagLog $log): array
    {
        $seconds = (int) config('rfid.movement_window_seconds');

        return [$log->detected_at->copy()->subSeconds($seconds), $log->detected_at->copy()->addSeconds($seconds)];
    }
}

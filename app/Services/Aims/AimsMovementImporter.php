<?php

namespace App\Services\Aims;

use App\Models\AssetMovement;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Turns AIMS LOGS rows (one row per equipment trip: TIME_OUT, and later TIME_IN) into EMMS equipment trips.
 *
 * AIMS has no per-tag scan history, so only trips are copied; asset_tag_logs is left alone.
 *
 *  - A log belongs to an AIMS PARENT (ITEM_ID). It is matched to EMMS equipment through the AIMS name, with the
 *    same aliases the tag import uses. Accessories, other asset types and unmatched names are skipped.
 *  - Complete trips and trips with only a time in are copied as they are.
 *  - A trip that went out and was never closed is copied only when it is the equipment's most recent movement,
 *    because AIMS leaves thousands of stale open rows (an IN scan that does not find its OUT adds a new row).
 *  - Verification is kept: acknowledged in AIMS stays acknowledged (complete trips only; in EMMS an
 *    acknowledgement means both times were checked), manual in / out keep who did it.
 */
class AimsMovementImporter
{
    public const SKIP_ALREADY_IMPORTED = 'already imported';

    public const SKIP_DELETED = 'deleted in AIMS';

    public const SKIP_ACCESSORY = 'belongs to an accessory (only equipment is tracked)';

    public const SKIP_NO_ASSET = 'ITEM_ID is not an AIMS parent or accessory';

    public const SKIP_NO_UNIT = 'no EMMS unit with matching eqm_prc_code';

    public const SKIP_AMBIGUOUS = 'eqm_prc_code matches more than one EMMS unit';

    public const SKIP_NOT_EQUIPMENT = 'not equipment in EMMS (only equipment is tracked)';

    public const SKIP_STALE_OPEN = 'never closed, and the equipment has a later movement or a newer open trip';

    public const SKIP_BAD_TIMES = 'time in is before time out';

    public const SKIP_FUTURE = 'a time is in the future';

    public const SKIP_NO_TIMES = 'no time out and no time in';

    public function __construct(private readonly AimsAssetTagImporter $codes) {}

    /**
     * @param  Collection<int, object>  $logs  AIMS LOGS rows in the window
     * @param  Collection<int, object>  $parents  AIMS PARENT rows (ID, NAME)
     * @param  Collection<int, object>  $accessories  AIMS ACCESSORIES rows (ID)
     * @param  array<string, list<array{id: int, type: ?string}>>  $unitsByCode  normalized eqm_prc_code => EMMS units
     * @param  array<string, string>  $aliases  AIMS code => EMMS code (as written)
     * @param  array<int, int>  $userMap  AIMS user id => EMMS user id
     * @param  array<int, int>  $locationMap  AIMS location id => EMMS location id
     * @param  array<int, true>  $alreadyImported  AIMS log ids already in asset_movements
     * @return array{rows: list<array<string, mixed>>, skipped: list<array{aims_id: int, reason: string, asset: ?string, time_out: ?string, time_in: ?string}>, unmappedUsers: list<int>}
     */
    public function plan(Collection $logs, Collection $parents, Collection $accessories, array $unitsByCode, array $aliases, array $userMap, array $locationMap, array $alreadyImported = []): array
    {
        $parentsById = $parents->keyBy('ID');
        $accessoryIds = $accessories->pluck('ID')->flip();
        $aliasMap = collect($aliases)
            ->mapWithKeys(fn (string $emmsCode, string $aimsCode): array => [$this->codes->normalizeCode($aimsCode) => $this->codes->normalizeCode($emmsCode)])
            ->all();

        $skipped = [];
        $candidates = [];
        $unmappedUsers = [];
        $skip = function (object $log, string $reason, ?string $asset = null) use (&$skipped): void {
            $skipped[] = [
                'aims_id' => (int) $log->ID,
                'reason' => $reason,
                'asset' => $asset,
                'time_out' => $this->time($log->TIME_OUT ?? null),
                'time_in' => $this->time($log->TIME_IN ?? null),
            ];
        };

        foreach ($logs as $log) {
            $timeOut = $this->time($log->TIME_OUT ?? null);
            $timeIn = $this->time($log->TIME_IN ?? null);
            $parent = $parentsById->get($log->ITEM_ID);
            $name = $parent?->NAME !== null ? trim((string) $parent->NAME) : null;

            $reason = match (true) {
                isset($alreadyImported[(int) $log->ID]) => self::SKIP_ALREADY_IMPORTED,
                (int) ($log->IS_DELETED ?? 0) === 1 => self::SKIP_DELETED,
                $timeOut === null && $timeIn === null => self::SKIP_NO_TIMES,
                $parent === null && $accessoryIds->has($log->ITEM_ID) => self::SKIP_ACCESSORY,
                $parent === null => self::SKIP_NO_ASSET,
                default => null,
            };

            if ($reason !== null) {
                $skip($log, $reason, $name);

                continue;
            }

            $code = $this->codes->normalizeCode($name ?? '');
            $units = $unitsByCode[$aliasMap[$code] ?? $code] ?? [];

            $reason = match (true) {
                count($units) === 0 => self::SKIP_NO_UNIT,
                count($units) > 1 => self::SKIP_AMBIGUOUS,
                $units[0]['type'] !== 'EQM' => self::SKIP_NOT_EQUIPMENT,
                $timeOut !== null && $timeIn !== null && $timeIn < $timeOut => self::SKIP_BAD_TIMES,
                ($timeOut !== null && $timeOut > now()->format('Y-m-d H:i:s')) || ($timeIn !== null && $timeIn > now()->format('Y-m-d H:i:s')) => self::SKIP_FUTURE,
                default => null,
            };

            if ($reason !== null) {
                $skip($log, $reason, $name);

                continue;
            }

            $candidates[] = ['log' => $log, 'unit' => $units[0]['id'], 'out' => $timeOut, 'in' => $timeIn, 'name' => $name];
        }

        // A never-closed trip only counts when it is the equipment's latest movement: nothing later, no newer open trip.
        $latestMoment = [];
        $latestOpen = [];
        foreach ($candidates as $candidate) {
            $unit = $candidate['unit'];
            $moment = $candidate['out'] ?? $candidate['in'];
            $latestMoment[$unit] = max($latestMoment[$unit] ?? '', $moment);

            if ($candidate['out'] !== null && $candidate['in'] === null) {
                $key = $candidate['out'].'|'.str_pad((string) $candidate['log']->ID, 12, '0', STR_PAD_LEFT);
                $latestOpen[$unit] = max($latestOpen[$unit] ?? '', $key);
            }
        }

        $rows = [];
        foreach ($candidates as $candidate) {
            $log = $candidate['log'];
            $isOpen = $candidate['out'] !== null && $candidate['in'] === null;

            if ($isOpen) {
                $key = $candidate['out'].'|'.str_pad((string) $log->ID, 12, '0', STR_PAD_LEFT);

                if ($key !== $latestOpen[$candidate['unit']] || $latestMoment[$candidate['unit']] > $candidate['out']) {
                    $skip($log, self::SKIP_STALE_OPEN, $candidate['name']);

                    continue;
                }
            }

            $rows[] = $this->row($candidate, $userMap, $locationMap, $unmappedUsers);
        }

        return ['rows' => $rows, 'skipped' => $skipped, 'unmappedUsers' => array_values(array_unique($unmappedUsers))];
    }

    /**
     * Inserts the planned trips in chunks, skipping any AIMS log that is already there.
     *
     * @param  list<array<string, mixed>>  $rows
     * @return int how many were inserted
     */
    public function persist(array $rows): int
    {
        $inserted = 0;

        DB::transaction(function () use ($rows, &$inserted): void {
            foreach (array_chunk($rows, 500) as $chunk) {
                $inserted += DB::table('asset_movements')->insertOrIgnore($chunk);
            }
        });

        Cache::forget(AssetMovement::STATUS_COUNTS_CACHE_KEY);

        return $inserted;
    }

    /**
     * Removes every trip that came from AIMS (and only those).
     */
    public function rollback(): int
    {
        $deleted = DB::table('asset_movements')->whereNotNull('aims_log_id')->delete();

        Cache::forget(AssetMovement::STATUS_COUNTS_CACHE_KEY);

        return $deleted;
    }

    /**
     * @param  array{log: object, unit: int, out: ?string, in: ?string, name: ?string}  $candidate
     * @param  array<int, int>  $userMap
     * @param  array<int, int>  $locationMap
     * @param  list<int>  $unmappedUsers
     * @return array<string, mixed>
     */
    private function row(array $candidate, array $userMap, array $locationMap, array &$unmappedUsers): array
    {
        $log = $candidate['log'];
        $complete = $candidate['out'] !== null && $candidate['in'] !== null;
        $user = function (mixed $aimsId) use ($userMap, &$unmappedUsers): ?int {
            if (blank($aimsId)) {
                return null;
            }

            if (! isset($userMap[(int) $aimsId])) {
                $unmappedUsers[] = (int) $aimsId;

                return null;
            }

            return $userMap[(int) $aimsId];
        };

        $manualOut = (int) ($log->IS_MANUAL_OUT ?? 0) === 1 && $candidate['out'] !== null;
        $manualIn = (int) ($log->IS_MANUAL_IN ?? 0) === 1 && $candidate['in'] !== null;
        $acknowledgedBy = $complete ? $user($log->ACKNOWLEDGED_BY ?? null) : null;
        $createdAt = $this->time($log->CREATED_DATE ?? null) ?? ($candidate['out'] ?? $candidate['in']);

        return [
            'asset_id' => $candidate['unit'],
            'tag_id' => null,
            'out_log_id' => null,
            'in_log_id' => null,
            'aims_log_id' => (int) $log->ID,
            'time_out' => $candidate['out'],
            'time_in' => $candidate['in'],
            'yard_out_id' => $candidate['out'] !== null ? ($locationMap[(int) ($log->LOCATION_OUT ?? 0)] ?? null) : null,
            'yard_in_id' => $candidate['in'] !== null ? ($locationMap[(int) ($log->LOCATION_IN ?? 0)] ?? null) : null,
            'manual_out_by' => $manualOut ? $user($log->MANUAL_OUT_BY ?? null) : null,
            'manual_out_at' => null,
            'manual_out_remarks' => $manualOut ? 'Entered manually in AIMS' : null,
            'manual_in_by' => $manualIn ? $user($log->MANUAL_IN_BY ?? null) : null,
            'manual_in_at' => null,
            'manual_in_remarks' => $manualIn ? 'Entered manually in AIMS' : null,
            'acknowledged_by' => $acknowledgedBy,
            'acknowledged_at' => $acknowledgedBy !== null ? ($this->time($log->ACKNOWLEDGED_DATE ?? null) ?? $createdAt) : null,
            'acknowledgement_remarks' => $acknowledgedBy !== null ? 'Acknowledged in AIMS' : null,
            'disregarded_by' => null,
            'disregarded_at' => null,
            'disregard_remarks' => null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    private function time(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse((string) $value)->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return null;
        }
    }
}

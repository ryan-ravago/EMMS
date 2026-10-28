<?php

namespace App\Services\Aims;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Maps AIMS RFIDTbl rows onto EMMS equipment units and writes them to asset_tags.
 *
 * AIMS RFIDTbl.ASSIGNED_TO has no foreign key: it holds an id from either PARENT or
 * ACCESSORIES (the two id ranges never overlap). EMMS units are matched through the AIMS
 * NAME against equipment_units.eqm_prc_code:
 *
 *  - every asset keeps only its own tags: a tag on an accessory goes to the accessory's own EMMS unit when
 *    that accessory exists in EMMS as an asset of its own, and is skipped otherwise (never moved to its parent). "Its own" is decided by identity (the AIMS
 *    accessory id it was created from), or by name only when no other AIMS accessory has that name:
 *    many cranes have a "CWT #2 (10T)", and their tags must not land on one crane's counterweight;
 *  - an alias (AIMS code => EMMS code) is used for assets renamed in EMMS, e.g. KLT80V#1 => KLT80V01;
 *  - when several EMMS codes look alike (XGC55 #1 / XGC55#1), the exactly matching code wins.
 *
 * Anything that still cannot be matched unambiguously is skipped and reported.
 */
class AimsAssetTagImporter
{
    public const string SKIP_NO_ASSET = 'ASSIGNED_TO not found in PARENT or ACCESSORIES';

    public const string SKIP_ASSET_DELETED = 'asset is deleted in AIMS';

    public const string SKIP_NO_UNIT = 'no EMMS unit with matching eqm_prc_code';

    public const string SKIP_AMBIGUOUS_UNIT = 'eqm_prc_code matches more than one EMMS unit';

    public const string SKIP_TAG_CONFLICT = 'same RFID assigned to different units in AIMS';

    public const string SKIP_BLANK_TAG = 'blank RFID';

    public const string SKIP_ACCESSORY_NOT_ASSET = 'tag belongs to an accessory that is not an asset in EMMS';

    /** @var array<string, list<int>> normalized eqm_prc_code => eqm_ids */
    private array $unitsByCode = [];

    /** @var array<string, int> exact (trimmed, lower case) eqm_prc_code => eqm_id */
    private array $unitsByExactCode = [];

    /** @var array<string, string> normalized AIMS code => exact (trimmed, lower case) EMMS code */
    private array $aliases = [];

    /**
     * @param  Collection<int, object{ID: int, RFID: string, ASSIGNED_TO: int, ASSIGNED_BY?: ?int, ASSIGNED_DATE?: ?string, SCANNED_BY?: ?int, SCANNED_DATE?: ?string, IS_DELETE?: mixed}>  $tags
     * @param  Collection<int, object{ID: int, NAME: string, IS_DELETE: int|string|null}>  $parents
     * @param  Collection<int, object{ID: int, PARENT_ID: int, NAME?: string, IS_DELETE: int|string|null}>  $accessories
     * @param  array<string, list<int>>  $unitsByCode  normalized eqm_prc_code => eqm_ids
     * @param  array<string, int>  $unitsByExactCode  trimmed lower-case eqm_prc_code => eqm_id
     * @param  array<string, string>  $aliases  AIMS code => EMMS code (both as written; normalized here)
     * @param  array<int, int>  $accessoryUnits  AIMS accessory id => eqm_id, for accessories that were added to EMMS as assets
     * @return array{rows: array<string, int>, meta: array<string, array{assigned_by: ?int, assigned_date: ?string, scanned_by: ?int, scanned_date: ?string, is_deleted: bool}>, skipped: list<array{aims_id: int, rfid: string, assigned_to: int, reason: string}>, duplicates: int, from_accessory_records: int}
     */
    public function plan(Collection $tags, Collection $parents, Collection $accessories, array $unitsByCode, array $unitsByExactCode = [], array $aliases = [], array $accessoryUnits = []): array
    {
        $this->unitsByCode = $unitsByCode;
        $this->unitsByExactCode = $unitsByExactCode;
        $this->aliases = collect($aliases)
            ->mapWithKeys(fn (string $emmsCode, string $aimsCode): array => [$this->normalizeCode($aimsCode) => $this->exactCode($emmsCode)])
            ->all();

        $parentsById = $parents->keyBy('ID');
        $accessoriesById = $accessories->keyBy('ID');
        $accessoryNameCounts = $accessories
            ->reject(fn (object $accessory): bool => $this->isDeleted($accessory))
            ->countBy(fn (object $accessory): string => $this->normalizeCode((string) ($accessory->NAME ?? '')));

        $rows = [];
        $meta = [];
        $skipped = [];
        $conflicted = [];
        $firstSeen = [];
        $duplicates = 0;
        $fromAccessoryRecords = 0;

        foreach ($tags as $tag) {
            $tagId = $this->normalizeTag((string) $tag->RFID);
            $source = [
                'aims_id' => (int) $tag->ID,
                'rfid' => (string) $tag->RFID,
                'assigned_to' => (int) $tag->ASSIGNED_TO,
            ];
            $skip = function (string $reason) use (&$skipped, $source): void {
                $skipped[] = $source + ['reason' => $reason];
            };

            if ($tagId === '') {
                $skip(self::SKIP_BLANK_TAG);

                continue;
            }

            $via = 'parent';
            $unitId = null;
            $parent = $parentsById->get($tag->ASSIGNED_TO);

            if ($parent === null && $accessoriesById->has($tag->ASSIGNED_TO)) {
                $accessory = $accessoriesById->get($tag->ASSIGNED_TO);

                if ($this->isDeleted($accessory)) {
                    $skip(self::SKIP_ASSET_DELETED);

                    continue;
                }

                $ownUnit = $accessoryUnits[(int) $accessory->ID] ?? null;
                $name = (string) ($accessory->NAME ?? '');

                // A reviewed alias is trusted even when AIMS has the same item twice (e.g. MVB2 01 under two containers).
                if ($ownUnit === null && (isset($this->aliases[$this->normalizeCode($name)]) || ($accessoryNameCounts[$this->normalizeCode($name)] ?? 0) === 1)) {
                    [$ownUnit] = $this->resolveUnit($name);
                }

                // Every asset keeps only its own tags. An accessory that is not an asset in EMMS has no record
                // to hold its tags, and they are never pushed up onto its parent.
                if ($ownUnit === null) {
                    $skip(self::SKIP_ACCESSORY_NOT_ASSET);

                    continue;
                }

                $unitId = $ownUnit;
                $via = 'accessory record';
            }

            if ($unitId === null) {
                if ($parent === null) {
                    $skip(self::SKIP_NO_ASSET);

                    continue;
                }

                if ($this->isDeleted($parent)) {
                    $skip(self::SKIP_ASSET_DELETED);

                    continue;
                }

                [$unitId, $reason] = $this->resolveUnit((string) $parent->NAME);

                if ($unitId === null) {
                    $skip($reason);

                    continue;
                }
            }

            if (isset($conflicted[$tagId])) {
                $skip(self::SKIP_TAG_CONFLICT);

                continue;
            }

            if (isset($rows[$tagId])) {
                if ($rows[$tagId] === $unitId) {
                    $duplicates++;
                } else {
                    unset($rows[$tagId], $meta[$tagId]);
                    $conflicted[$tagId] = true;
                    $skipped[] = $firstSeen[$tagId] + ['reason' => self::SKIP_TAG_CONFLICT];
                    $skip(self::SKIP_TAG_CONFLICT);
                }

                continue;
            }

            $rows[$tagId] = $unitId;
            $meta[$tagId] = [
                'assigned_by' => isset($tag->ASSIGNED_BY) ? (int) $tag->ASSIGNED_BY : null,
                'assigned_date' => $this->dateTime($tag->ASSIGNED_DATE ?? null),
                'scanned_by' => isset($tag->SCANNED_BY) ? (int) $tag->SCANNED_BY : null,
                'scanned_date' => $this->dateTime($tag->SCANNED_DATE ?? null),
                'is_deleted' => (bool) (int) ($tag->IS_DELETE ?? 0),
            ];
            $firstSeen[$tagId] = $source;
            $fromAccessoryRecords += $via === 'accessory record' ? 1 : 0;
        }

        return [
            'rows' => $rows,
            'meta' => $meta,
            'skipped' => $skipped,
            'duplicates' => $duplicates,
            'from_accessory_records' => $fromAccessoryRecords,
        ];
    }

    /**
     * Inserts the planned tags, leaving any tag that already exists untouched.
     * AIMS user ids are translated to EMMS user ids through $userMap; unknown users are stored as null.
     * Uses the query builder, so no per-row activity log entries are written.
     *
     * @param  array<string, int>  $rows  tag_id => asset_parent_id
     * @param  array<string, array{assigned_by: ?int, assigned_date: ?string, scanned_by: ?int, scanned_date: ?string, is_deleted: bool}>  $meta
     * @param  array<int, int>  $userMap  AIMS user id => EMMS user id
     * @return int number of tags actually inserted
     */
    public function persist(array $rows, array $meta = [], array $userMap = []): int
    {
        $inserted = 0;

        DB::transaction(function () use ($rows, $meta, $userMap, &$inserted): void {
            foreach (array_chunk($rows, 500, preserve_keys: true) as $chunk) {
                $payload = [];

                foreach ($chunk as $tagId => $unitId) {
                    $m = $meta[$tagId] ?? [];
                    $payload[] = [
                        'tag_id' => (string) $tagId,
                        'asset_parent_id' => $unitId,
                        'assigned_by' => isset($m['assigned_by']) ? ($userMap[$m['assigned_by']] ?? null) : null,
                        'assigned_date' => $m['assigned_date'] ?? null,
                        'scanned_date' => $m['scanned_date'] ?? null,
                        'scanned_by' => isset($m['scanned_by']) ? ($userMap[$m['scanned_by']] ?? null) : null,
                        'is_deleted' => (bool) ($m['is_deleted'] ?? false),
                    ];
                }

                $inserted += DB::table('asset_tags')->insertOrIgnore($payload);
            }
        });

        return $inserted;
    }

    public function normalizeTag(string $tag): string
    {
        return mb_strtolower(trim($tag));
    }

    public function normalizeCode(string $code): string
    {
        return mb_strtolower((string) preg_replace('/\s+/', '', $code));
    }

    public function exactCode(string $code): string
    {
        return mb_strtolower(trim($code));
    }

    /**
     * @return array{0: ?int, 1: ?string} the unit id, or null and the skip reason
     */
    private function resolveUnit(string $aimsName): array
    {
        if (trim($aimsName) === '') {
            return [null, self::SKIP_NO_UNIT];
        }

        $code = $this->normalizeCode($aimsName);

        if (isset($this->aliases[$code])) {
            $unitId = $this->unitsByExactCode[$this->aliases[$code]] ?? null;

            return $unitId === null ? [null, self::SKIP_NO_UNIT] : [$unitId, null];
        }

        $unitIds = $this->unitsByCode[$code] ?? [];

        if (count($unitIds) === 1) {
            return [$unitIds[0], null];
        }

        if (count($unitIds) > 1) {
            $exact = $this->unitsByExactCode[$this->exactCode($aimsName)] ?? null;

            return $exact !== null && in_array($exact, $unitIds, true) ? [$exact, null] : [null, self::SKIP_AMBIGUOUS_UNIT];
        }

        return [null, self::SKIP_NO_UNIT];
    }

    private function dateTime(mixed $value): ?string
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

    private function isDeleted(object $asset): bool
    {
        return (int) ($asset->IS_DELETE ?? 0) === 1;
    }
}

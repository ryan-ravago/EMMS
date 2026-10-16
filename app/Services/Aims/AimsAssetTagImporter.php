<?php

namespace App\Services\Aims;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Maps AIMS RFIDTbl rows onto EMMS equipment units and writes them to asset_tags.
 *
 * AIMS RFIDTbl.ASSIGNED_TO has no foreign key: it holds an id from either PARENT or
 * ACCESSORIES (the two id ranges never overlap). EMMS units are matched through the
 * AIMS parent's NAME against equipment_units.eqm_prc_code; an accessory's tag is attached to
 * its parent's unit. Anything that cannot be matched unambiguously is skipped and reported.
 */
class AimsAssetTagImporter
{
    public const string SKIP_NO_ASSET = 'ASSIGNED_TO not found in PARENT or ACCESSORIES';

    public const string SKIP_ASSET_DELETED = 'asset is deleted in AIMS';

    public const string SKIP_NO_UNIT = 'no EMMS unit with matching eqm_prc_code';

    public const string SKIP_AMBIGUOUS_UNIT = 'eqm_prc_code matches more than one EMMS unit';

    public const string SKIP_TAG_CONFLICT = 'same RFID assigned to different units in AIMS';

    public const string SKIP_BLANK_TAG = 'blank RFID';

    /**
     * @param  Collection<int, object{ID: int, RFID: string, ASSIGNED_TO: int}>  $tags
     * @param  Collection<int, object{ID: int, NAME: string, IS_DELETE: int|string|null}>  $parents
     * @param  Collection<int, object{ID: int, PARENT_ID: int, IS_DELETE: int|string|null}>  $accessories
     * @param  array<string, list<int>>  $unitsByCode  normalized eqm_prc_code => eqm_ids
     * @return array{rows: array<string, int>, skipped: list<array{aims_id: int, rfid: string, assigned_to: int, reason: string}>, duplicates: int, from_accessories: int}
     */
    public function plan(Collection $tags, Collection $parents, Collection $accessories, array $unitsByCode): array
    {
        $parentsById = $parents->keyBy('ID');
        $accessoriesById = $accessories->keyBy('ID');

        $rows = [];
        $skipped = [];
        $conflicted = [];
        $firstSeen = [];
        $duplicates = 0;
        $fromAccessories = 0;

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

            $viaAccessory = false;
            $parent = $parentsById->get($tag->ASSIGNED_TO);

            if ($parent === null && $accessoriesById->has($tag->ASSIGNED_TO)) {
                $accessory = $accessoriesById->get($tag->ASSIGNED_TO);
                $viaAccessory = true;

                if ($this->isDeleted($accessory)) {
                    $skip(self::SKIP_ASSET_DELETED);

                    continue;
                }

                $parent = $parentsById->get($accessory->PARENT_ID);
            }

            if ($parent === null) {
                $skip(self::SKIP_NO_ASSET);

                continue;
            }

            if ($this->isDeleted($parent)) {
                $skip(self::SKIP_ASSET_DELETED);

                continue;
            }

            $unitIds = $unitsByCode[$this->normalizeCode((string) $parent->NAME)] ?? [];

            if ($unitIds === []) {
                $skip(self::SKIP_NO_UNIT);

                continue;
            }

            if (count($unitIds) > 1) {
                $skip(self::SKIP_AMBIGUOUS_UNIT);

                continue;
            }

            $unitId = $unitIds[0];

            if (isset($conflicted[$tagId])) {
                $skip(self::SKIP_TAG_CONFLICT);

                continue;
            }

            if (isset($rows[$tagId])) {
                if ($rows[$tagId] === $unitId) {
                    $duplicates++;
                } else {
                    unset($rows[$tagId]);
                    $conflicted[$tagId] = true;
                    $skipped[] = $firstSeen[$tagId] + ['reason' => self::SKIP_TAG_CONFLICT];
                    $skip(self::SKIP_TAG_CONFLICT);
                }

                continue;
            }

            $rows[$tagId] = $unitId;
            $firstSeen[$tagId] = $source;
            $fromAccessories += $viaAccessory ? 1 : 0;
        }

        return [
            'rows' => $rows,
            'skipped' => $skipped,
            'duplicates' => $duplicates,
            'from_accessories' => $fromAccessories,
        ];
    }

    /**
     * Inserts the planned tags, leaving any tag that already exists untouched.
     * Uses the query builder, so no per-row activity log entries are written.
     *
     * @param  array<string, int>  $rows  tag_id => asset_parent_id
     * @return int number of tags actually inserted
     */
    public function persist(array $rows): int
    {
        $inserted = 0;

        DB::transaction(function () use ($rows, &$inserted): void {
            foreach (array_chunk($rows, 500, preserve_keys: true) as $chunk) {
                $payload = [];

                foreach ($chunk as $tagId => $unitId) {
                    $payload[] = ['tag_id' => (string) $tagId, 'asset_parent_id' => $unitId];
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

    private function isDeleted(object $asset): bool
    {
        return (int) ($asset->IS_DELETE ?? 0) === 1;
    }
}

<?php

namespace App\Support;

use App\Models\Equipment;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Housekeeping for the SAP equipment sync.
 *
 * Shared by the scheduled job and the two manual "Sync from SAP" buttons so they all
 * treat records that disappeared from SAP the same way.
 */
class SapEquipmentCleanup
{
    /**
     * Delete synced records (those with a PRC code) that are no longer in SAP and have no
     * related data. Records that are still in use are left for the caller to deactivate.
     *
     * "Related data" is anything the database would refuse to delete along with the row:
     * work orders, inspections, maintenance tasks, task schedules, asset tags, lifecycle
     * history, accessories, and so on, found by looking at the foreign keys that point at
     * the equipment table, so a table added later is covered automatically. An accessory
     * that is allocated to equipment also counts as in use. Rows that cascade on delete
     * (edit logs, category tags) are not related data; they go with the record.
     *
     * @param  iterable<int, string>  $sapPrcCodes  every PRC code SAP returned
     * @return array<int, string> the PRC codes that were deleted
     */
    public static function deleteUnusedMissingFromSap(iterable $sapPrcCodes): array
    {
        $inSap = [];

        foreach ($sapPrcCodes as $code) {
            $inSap[(string) $code] = true;
        }

        // Without a SAP list there is nothing to compare against, so never purge.
        if ($inSap === []) {
            return [];
        }

        $missingIds = Equipment::query()
            ->whereNotNull('eqm_prc_code')
            ->whereNull('parent_id')
            ->pluck('eqm_prc_code', 'eqm_id')
            ->reject(fn ($code) => isset($inSap[(string) $code]))
            ->keys();

        if ($missingIds->isEmpty()) {
            return [];
        }

        $references = self::blockingReferences();
        $deleted = [];

        foreach ($missingIds->chunk(500) as $chunk) {
            $ids = $chunk->all();
            $inUse = [];

            foreach ($references as [$table, $column]) {
                foreach (DB::table($table)->whereIn($column, $ids)->distinct()->pluck($column) as $id) {
                    $inUse[(int) $id] = true;
                }
            }

            $deletable = array_values(array_filter($ids, fn ($id) => ! isset($inUse[(int) $id])));

            if ($deletable === []) {
                continue;
            }

            // One by one through Eloquent so each deletion is audited. A row that gained
            // related data since the check above is kept, not allowed to fail the whole sync.
            foreach (Equipment::query()->whereKey($deletable)->get() as $equipment) {
                try {
                    $equipment->delete();
                    $deleted[] = $equipment->eqm_prc_code;
                } catch (QueryException $e) {
                    Log::warning("SAP sync kept equipment [{$equipment->eqm_prc_code}] because it is still referenced: ".$e->getMessage());
                }
            }
        }

        return $deleted;
    }

    /**
     * Every [table, column] holding a foreign key to the equipment table that does not
     * cascade on delete.
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private static function blockingReferences(): array
    {
        $equipment = new Equipment;

        return DB::table('information_schema.KEY_COLUMN_USAGE as k')
            ->join('information_schema.REFERENTIAL_CONSTRAINTS as r', function ($join) {
                $join->on('r.CONSTRAINT_SCHEMA', '=', 'k.CONSTRAINT_SCHEMA')
                    ->on('r.CONSTRAINT_NAME', '=', 'k.CONSTRAINT_NAME')
                    ->on('r.TABLE_NAME', '=', 'k.TABLE_NAME');
            })
            ->where('k.REFERENCED_TABLE_SCHEMA', DB::getDatabaseName())
            ->where('k.REFERENCED_TABLE_NAME', $equipment->getTable())
            ->where('k.REFERENCED_COLUMN_NAME', $equipment->getKeyName())
            ->where('r.DELETE_RULE', '<>', 'CASCADE')
            ->get(['k.TABLE_NAME as table_name', 'k.COLUMN_NAME as column_name'])
            ->map(fn ($row) => [$row->table_name, $row->column_name])
            ->all();
    }
}

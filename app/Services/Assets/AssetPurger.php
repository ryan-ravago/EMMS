<?php

namespace App\Services\Assets;

use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Permanently deletes assets (equipment_units rows) together with everything that belongs to them:
 * work orders and their logs, updates, notes, assignments and reports, lifecycle and edit logs,
 * RFID tags and their logs, maintenance tasks and schedules, old-module inspections, tag/category
 * links and the activity log rows about all of these.
 *
 * Rows that only point at a deleted record but belong to something else (another asset's accessory,
 * a log of another work order) are detached instead of deleted. Text snapshots that hold no id
 * (closed AppSheet inspections) are left alone: they would come back on the next sync anyway.
 *
 * Deletes go through the query builder, so no model events fire and no per-row activity is logged.
 */
class AssetPurger
{
    /**
     * Activity log subject types per kind of record.
     *
     * @var array<string, list<string>>
     */
    private const ACTIVITY_SUBJECTS = [
        'assets' => ['App\Models\Equipment'],
        'workOrders' => ['App\Models\WorkOrder', 'App\Models\RequestorWorkOrder', 'App\Models\TechnicianWorkOrder'],
        'workOrderLogs' => ['App\Models\WorkOrderLog'],
        'maintenanceTasks' => ['App\Models\MaintenanceTask'],
        'inspections' => ['App\Models\Inspection'],
        'inspectionItems' => ['App\Models\InspectionItem'],
    ];

    /**
     * Codes are compared as text. Spacing differences ("LB 01" vs "LB  01") are tolerated as a second
     * pass, but only when they point at exactly one asset.
     */
    public function normalizeCode(string $code): string
    {
        return (string) preg_replace('/\s+/u', ' ', trim($code));
    }

    /**
     * @param  list<string>  $codes
     * @param  iterable<object{eqm_id: int|string, eqm_prc_code: ?string}>  $units
     * @return array{exact: array<string, int>, spacing: array<string, array{id: int, code: string}>, ambiguous: array<string, list<string>>, missing: list<string>}
     */
    public function matchCodes(array $codes, iterable $units): array
    {
        $byCode = [];
        $byNormalized = [];

        foreach ($units as $unit) {
            $code = (string) $unit->eqm_prc_code;

            if ($code === '') {
                continue;
            }

            $byCode[$code][] = (int) $unit->eqm_id;
            $byNormalized[$this->normalizeCode($code)][$code] = (int) $unit->eqm_id;
        }

        $result = ['exact' => [], 'spacing' => [], 'ambiguous' => [], 'missing' => []];

        foreach (array_unique(array_map('strval', $codes)) as $code) {
            if ($code === '') {
                continue;
            }

            if (count($byCode[$code] ?? []) === 1) {
                $result['exact'][$code] = $byCode[$code][0];

                continue;
            }

            if (count($byCode[$code] ?? []) > 1) {
                $result['ambiguous'][$code] = array_fill(0, count($byCode[$code]), $code);

                continue;
            }

            $candidates = $byNormalized[$this->normalizeCode($code)] ?? [];

            if (count($candidates) === 1) {
                $result['spacing'][$code] = ['id' => reset($candidates), 'code' => (string) key($candidates)];
            } elseif (count($candidates) > 1) {
                $result['ambiguous'][$code] = array_map('strval', array_keys($candidates));
            } else {
                $result['missing'][] = $code;
            }
        }

        return $result;
    }

    /**
     * How many rows each step would touch, without changing anything.
     *
     * @param  list<int>  $assetIds
     * @return array<string, int>
     */
    public function preview(array $assetIds): array
    {
        return $this->run($assetIds, execute: false);
    }

    /**
     * Deletes the assets and their related data in one transaction.
     *
     * @param  list<int>  $assetIds
     * @return array<string, int>
     */
    public function purge(array $assetIds): array
    {
        return DB::transaction(fn (): array => $this->run($assetIds, execute: true));
    }

    /**
     * @param  list<int>  $assetIds
     * @return array<string, int>
     */
    private function run(array $assetIds, bool $execute): array
    {
        $assetIds = array_values(array_unique(array_map('intval', $assetIds)));

        if ($assetIds === []) {
            return [];
        }

        $ids = $this->relatedIds($assetIds);
        $counts = [];

        foreach ($this->steps($ids) as $label => [$table, $scope, $detachColumn]) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $query = DB::table($table);
            $scope($query);

            $counts[$label] = $execute
                ? ($detachColumn === null ? $query->delete() : $query->update([$detachColumn => null]))
                : $query->count();
        }

        return $counts;
    }

    /**
     * Ids of everything that hangs off the assets, collected before anything is deleted.
     *
     * @param  list<int>  $assetIds
     * @return array<string, list<int>>
     */
    private function relatedIds(array $assetIds): array
    {
        $pluck = fn (string $table, string $key, string $column, array $values): array => Schema::hasTable($table) && $values !== []
            ? DB::table($table)->whereIn($column, $values)->pluck($key)->map(fn ($id) => (int) $id)->all()
            : [];

        $workOrders = $pluck('work_orders', 'wo_id', 'wo_eqm_id', $assetIds);
        $inspections = $pluck('inspections', 'ins_id', 'ins_eqm_id', $assetIds);

        return [
            'assets' => $assetIds,
            'workOrders' => $workOrders,
            'workOrderLogs' => $pluck('work_order_logs', 'wol_id', 'wol_wo_id', $workOrders),
            'reportSubmissions' => $pluck('report_submissions', 'rs_id', 'rs_wo_id', $workOrders),
            'maintenanceTasks' => $pluck('maintenance_tasks', 'mt_id', 'mt_eqm_id', $assetIds),
            'inspections' => $inspections,
            'inspectionItems' => $pluck('inspection_items', 'insi_id', 'insi_ins_id', $inspections),
            'tags' => array_map('strval', Schema::hasTable('asset_tags') ? DB::table('asset_tags')->whereIn('asset_parent_id', $assetIds)->pluck('tag_id')->all() : []),
        ];
    }

    /**
     * Children before parents, so no foreign key blocks a delete. A third element names the column
     * to clear instead of deleting the row.
     *
     * @param  array<string, list<int|string>>  $ids
     * @return array<string, array{0: string, 1: Closure(Builder): mixed, 2: ?string}>
     */
    private function steps(array $ids): array
    {
        $in = fn (string $column, string $key): Closure => fn (Builder $query) => $query->whereIn($column, $ids[$key] === [] ? [0] : $ids[$key]);

        return [
            'Worker reports' => ['worker_reports', $in('wr_rs_id', 'reportSubmissions'), null],
            'Report submissions' => ['report_submissions', $in('rs_wo_id', 'workOrders'), null],
            'Work order notes' => ['work_order_notes', $in('won_wo_id', 'workOrders'), null],
            'Work order assignments' => ['work_order_assignments', $in('woa_wo_id', 'workOrders'), null],
            'Work order log updates' => ['work_order_log_updates', $in('wolu_wo_id', 'workOrders'), null],
            'Work order logs' => ['work_order_logs', $in('wol_wo_id', 'workOrders'), null],
            'Inspection item logs' => ['inspection_item_logs', $in('inil_insi_id', 'inspectionItems'), null],
            'Inspection item logs (work order link cleared)' => ['inspection_item_logs', $in('inil_wo_id', 'workOrders'), 'inil_wo_id'],
            'Maintenance task logs' => ['maintenance_task_logs', $in('mtl_mt_id', 'maintenanceTasks'), null],
            'Maintenance task logs (work order link cleared)' => ['maintenance_task_logs', $in('mtl_wo_id', 'workOrders'), 'mtl_wo_id'],
            'Other work orders (maintenance task link cleared)' => ['work_orders', fn (Builder $query) => $in('wo_mt_id', 'maintenanceTasks')($query)->whereNotIn('wo_id', $ids['workOrders'] === [] ? [0] : $ids['workOrders']), 'wo_mt_id'],
            'Work orders' => ['work_orders', $in('wo_id', 'workOrders'), null],
            'Inspection items' => ['inspection_items', $in('insi_ins_id', 'inspections'), null],
            'Inspections' => ['inspections', $in('ins_id', 'inspections'), null],
            'Maintenance tasks' => ['maintenance_tasks', $in('mt_id', 'maintenanceTasks'), null],
            'Maintenance schedules' => ['equipment_tasks_schedules', $in('ets_eqm_id', 'assets'), null],
            'Lifecycle logs' => ['lifecycle_logs', $in('asset_id', 'assets'), null],
            'Edit logs' => ['asset_edit_logs', $in('asset_id', 'assets'), null],
            'RFID tag logs' => ['asset_tag_logs', fn (Builder $query) => $query->whereIn('tag_id', $ids['tags'] === [] ? [''] : $ids['tags']), null],
            'RFID tags' => ['asset_tags', $in('asset_parent_id', 'assets'), null],
            'Tag links' => ['equipment_unit_category', $in('eqm_id', 'assets'), null],
            'Category links' => ['equipment_category_equipment', $in('equipment_id', 'assets'), null],
            'Accessories of other assets (allocation cleared)' => ['equipment_units', fn (Builder $query) => $in('parent_id', 'assets')($query)->whereNotIn('eqm_id', $ids['assets']), 'parent_id'],
            'Activity log entries' => ['activity_log', fn (Builder $query) => $query->where(function (Builder $query) use ($ids): void {
                $query->whereRaw('1 = 0');

                foreach (self::ACTIVITY_SUBJECTS as $key => $types) {
                    if ($ids[$key] !== []) {
                        $query->orWhere(fn (Builder $query) => $query->whereIn('subject_type', $types)->whereIn('subject_id', $ids[$key]));
                    }
                }
            }), null],
            'Assets' => ['equipment_units', $in('eqm_id', 'assets'), null],
        ];
    }
}

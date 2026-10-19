<?php

namespace App\Services\Inspections;

use App\Models\AimsInspection;
use App\Models\AimsInspectionItem;
use App\Models\AimsInspectionLog;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\MaintenanceInspection;
use App\Models\MaintenanceInspectionItem;
use App\Models\MaintenanceInspectionLog;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Turns closed AppSheet inspections into plain-text rows: only Completed and Cancelled inspections are kept,
 * every code is replaced by its text, and nothing refers to another table. The planning methods are pure
 * (sheet rows in, rows to insert out); persist*() writes them and never touches an inspection twice.
 *
 * @phpstan-type Lookups array{
 *     statuses: array<string, string>,
 *     actions: array<string, string>,
 *     checklists: array<string, string>,
 *     departments: array<string, string>,
 *     equipment: array<int, array{prc: string|null, name: string|null}>
 * }
 * @phpstan-type Plan array{
 *     inspection_no: string,
 *     attributes: array<string, mixed>,
 *     items: list<array<string, mixed>>,
 *     logs: list<array<string, mixed>>
 * }
 */
class AppSheetInspectionSync
{
    /** Status ids that mean an inspection is finished and will not change any more. */
    public const CLOSED_STATUS_IDS = ['clrd', 'cncld'];

    private const FALLBACK_STATUS_TEXT = ['clrd' => 'Completed', 'cncld' => 'Cancelled'];

    public const MAINTENANCE_COLUMNS = [
        'insp_id', 'insp_no', 'insp_status_id', 'insp_dep_id', 'insp_eqm_id', 'insp_checklist_temp_id',
        'insp_asset_admin_manager_note', 'insp_req_by', 'insp_req_at', 'insp_manager_action_id',
        'insp_manager_action_by', 'insp_manager_technicians', 'insp_manager_verifiers', 'insp_manager_remarks',
        'insp_tech_action_id', 'insp_tech_action_by', 'insp_tech_remarks', 'insp_verifier_action_id',
        'insp_verifier_action_by', 'insp_verifier_remarks', 'insp_cancel_remarks',
    ];

    public const MAINTENANCE_ITEM_COLUMNS = ['inspi_insp_id', 'inspi_task', 'inspi_result', 'inspi_remarks'];

    public const MAINTENANCE_LOG_COLUMNS = ['inspl_insp_id', 'inspl_last_action', 'inspl_status', 'inspl_remarks', 'inspl_by', 'inspl_ts'];

    public const AIMS_COLUMNS = [
        'ainsp_id', 'ainsp_no', 'ainsp_status_id', 'ainsp_eqm_id', 'ainsp_checklist_temp_name', 'ainsp_lessee_name',
        'ainsp_jobsite_name', 'ainsp_operator_name', 'ainsp_day_duration', 'ainsp_by', 'ainsp_dt', 'ainsp_remarks',
        'ainsp_req_by', 'ainsp_req_dt', 'ainsp_remarks2', 'ainsp_cancel_remarks',
    ];

    public const AIMS_ITEM_COLUMNS = ['ainspi_insp_id', 'ainspi_task', 'ainspi_result', 'ainspi_remarks'];

    public const AIMS_LOG_COLUMNS = ['ainspl_insp_id', 'ainspl_last_action', 'ainspl_status', 'ainspl_remarks', 'ainspl_by', 'ainspl_ts'];

    public const STATUS_COLUMNS = ['status_id', 'status'];

    public const ACTION_COLUMNS = ['a_id', 'a_past_tense'];

    public const CHECKLIST_COLUMNS = ['chk_id', 'chk_name'];

    /**
     * @param  list<array<string, string|null>>  $statusRows
     * @param  list<array<string, string|null>>  $actionRows
     * @param  list<array<string, string|null>>  $checklistRows
     * @param  array<string, string>  $departments  upper-cased department code => department name
     * @param  array<int, array{prc: string|null, name: string|null}>  $equipment  eqm_id => PRC code and name
     * @return Lookups
     */
    public function buildLookups(array $statusRows, array $actionRows, array $checklistRows, array $departments, array $equipment): array
    {
        $pairs = fn (array $rows, string $key, string $value): array => collect($rows)
            ->filter(fn (array $row): bool => ($row[$key] ?? null) !== null && ($row[$value] ?? null) !== null)
            ->mapWithKeys(fn (array $row): array => [$row[$key] => $row[$value]])
            ->all();

        return [
            'statuses' => $pairs($statusRows, 'status_id', 'status'),
            'actions' => $pairs($actionRows, 'a_id', 'a_past_tense'),
            'checklists' => $pairs($checklistRows, 'chk_id', 'chk_name'),
            'departments' => $departments,
            'equipment' => $equipment,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function departmentLookup(): array
    {
        return Department::query()
            ->get(['dep_code', 'dep_name'])
            ->mapWithKeys(fn (Department $department): array => [strtoupper((string) $department->dep_code) => (string) $department->dep_name])
            ->all();
    }

    /**
     * @param  list<int|string|null>  $equipmentIds
     * @return array<int, array{prc: string|null, name: string|null}>
     */
    public function equipmentLookup(array $equipmentIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map(
            fn (mixed $id): ?int => is_numeric($id) ? (int) $id : null,
            $equipmentIds,
        ))));

        $lookup = [];

        foreach (array_chunk($ids, 500) as $chunk) {
            foreach (Equipment::query()->whereIn('eqm_id', $chunk)->get(['eqm_id', 'eqm_prc_code', 'eqm_name']) as $equipment) {
                $lookup[(int) $equipment->eqm_id] = ['prc' => $equipment->eqm_prc_code, 'name' => $equipment->eqm_name];
            }
        }

        return $lookup;
    }

    /**
     * @param  list<array<string, string|null>>  $inspections
     * @param  list<array<string, string|null>>  $items
     * @param  list<array<string, string|null>>  $logs
     * @param  Lookups  $lookups
     * @return array{plans: list<Plan>, open: int, invalid: int, unresolved_equipment: int}
     */
    public function planMaintenance(array $inspections, array $items, array $logs, array $lookups): array
    {
        $itemsByInspection = $this->groupBy($items, 'inspi_insp_id');
        $logsByInspection = $this->groupBy($logs, 'inspl_insp_id');

        return $this->plan($inspections, 'insp_id', 'insp_no', 'insp_status_id', 'insp_eqm_id', $lookups, function (array $row, array $equipment, array $planLogs) use ($lookups): array {
            $departmentCode = $row['insp_dep_id'];

            return [
                'department_code' => $departmentCode,
                'department_name' => $departmentCode === null ? null : ($lookups['departments'][strtoupper($departmentCode)] ?? null),
                'equipment_prc_code' => $equipment['prc'],
                'equipment_name' => $equipment['name'],
                'checklist_name' => $this->lookup($lookups['checklists'], $row['insp_checklist_temp_id']),
                'requested_by' => $row['insp_req_by'],
                'requested_at' => $this->formatDate($row['insp_req_at']),
                'asset_admin_manager_note' => $row['insp_asset_admin_manager_note'],
                'manager_action' => $this->lookup($lookups['actions'], $row['insp_manager_action_id']),
                'manager_action_by' => $row['insp_manager_action_by'],
                'manager_remarks' => $row['insp_manager_remarks'],
                'technicians' => $this->normalizeList($row['insp_manager_technicians']),
                'verifiers' => $this->normalizeList($row['insp_manager_verifiers']),
                'tech_action' => $this->lookup($lookups['actions'], $row['insp_tech_action_id']),
                'tech_action_by' => $row['insp_tech_action_by'],
                'tech_remarks' => $row['insp_tech_remarks'],
                'verifier_action' => $this->lookup($lookups['actions'], $row['insp_verifier_action_id']),
                'verifier_action_by' => $row['insp_verifier_action_by'],
                'verifier_remarks' => $row['insp_verifier_remarks'],
                'cancel_remarks' => $row['insp_cancel_remarks'],
                'closed_at' => $this->lastLogTime($planLogs),
            ];
        }, $itemsByInspection, $logsByInspection, [
            'task' => 'inspi_task', 'result' => 'inspi_result', 'remarks' => 'inspi_remarks',
        ], [
            'action' => 'inspl_last_action', 'status' => 'inspl_status', 'remarks' => 'inspl_remarks',
            'performed_by' => 'inspl_by', 'logged_at' => 'inspl_ts',
        ]);
    }

    /**
     * @param  list<array<string, string|null>>  $inspections
     * @param  list<array<string, string|null>>  $items
     * @param  list<array<string, string|null>>  $logs
     * @param  Lookups  $lookups
     * @return array{plans: list<Plan>, open: int, invalid: int, unresolved_equipment: int}
     */
    public function planAims(array $inspections, array $items, array $logs, array $lookups): array
    {
        $itemsByInspection = $this->groupBy($items, 'ainspi_insp_id');
        $logsByInspection = $this->groupBy($logs, 'ainspl_insp_id');

        return $this->plan($inspections, 'ainsp_id', 'ainsp_no', 'ainsp_status_id', 'ainsp_eqm_id', $lookups, function (array $row, array $equipment, array $planLogs): array {
            return [
                'equipment_prc_code' => $equipment['prc'],
                'equipment_name' => $equipment['name'],
                'checklist_name' => $row['ainsp_checklist_temp_name'],
                'lessee' => $row['ainsp_lessee_name'],
                'jobsite' => $row['ainsp_jobsite_name'],
                'operator' => $row['ainsp_operator_name'],
                'day_duration' => is_numeric($row['ainsp_day_duration']) ? (int) $row['ainsp_day_duration'] : null,
                'inspected_by' => $row['ainsp_by'],
                'inspected_at' => $this->formatDate($row['ainsp_dt']),
                'remarks' => $row['ainsp_remarks'],
                'requested_by' => $row['ainsp_req_by'],
                'requested_at' => $this->formatDate($row['ainsp_req_dt']),
                'completion_remarks' => $row['ainsp_remarks2'],
                'cancel_remarks' => $row['ainsp_cancel_remarks'],
                'closed_at' => $this->lastLogTime($planLogs),
            ];
        }, $itemsByInspection, $logsByInspection, [
            'task' => 'ainspi_task', 'result' => 'ainspi_result', 'remarks' => 'ainspi_remarks',
        ], [
            'action' => 'ainspl_last_action', 'status' => 'ainspl_status', 'remarks' => 'ainspl_remarks',
            'performed_by' => 'ainspl_by', 'logged_at' => 'ainspl_ts',
        ]);
    }

    /**
     * Drops the plans whose inspection number is already stored: a closed inspection is imported once only.
     *
     * @param  class-string<MaintenanceInspection|AimsInspection>  $model
     * @param  list<Plan>  $plans
     * @return list<Plan>
     */
    public function onlyNew(string $model, array $plans): array
    {
        $existing = [];

        foreach (array_chunk(array_column($plans, 'inspection_no'), 500) as $chunk) {
            foreach ($model::query()->whereIn('inspection_no', $chunk)->pluck('inspection_no') as $number) {
                $existing[$number] = true;
            }
        }

        return array_values(array_filter($plans, fn (array $plan): bool => ! isset($existing[$plan['inspection_no']])));
    }

    /**
     * @param  list<Plan>  $plans
     * @return array{inserted: int, skipped: int, failed: int}
     */
    public function persistMaintenance(array $plans): array
    {
        return $this->persist(MaintenanceInspection::class, MaintenanceInspectionItem::class, MaintenanceInspectionLog::class, 'maintenance_inspection_id', $plans);
    }

    /**
     * @param  list<Plan>  $plans
     * @return array{inserted: int, skipped: int, failed: int}
     */
    public function persistAims(array $plans): array
    {
        return $this->persist(AimsInspection::class, AimsInspectionItem::class, AimsInspectionLog::class, 'aims_inspection_id', $plans);
    }

    /**
     * Parses the sheet's US-style date (month/day/year, 24-hour time), e.g. "10/2/2026 9:09:57" = 2 Oct 2026.
     */
    public static function parseSheetDate(?string $value): ?CarbonImmutable
    {
        $value = $value === null ? '' : trim($value);

        if ($value === '') {
            return null;
        }

        foreach (['n/j/Y G:i:s', 'n/j/Y G:i', 'n/j/Y'] as $format) {
            try {
                $date = CarbonImmutable::createFromFormat('!'.$format, $value);
            } catch (Throwable) {
                continue;
            }

            if ($date !== false && $date->format($format) === $value) {
                return $date;
            }
        }

        return null;
    }

    /**
     * @param  list<array<string, string|null>>  $inspections
     * @param  Lookups  $lookups
     * @param  callable(array<string, string|null>, array{prc: string|null, name: string|null}, list<array<string, mixed>>): array<string, mixed>  $headerAttributes
     * @param  array<string, list<array<string, string|null>>>  $itemsByInspection
     * @param  array<string, list<array<string, string|null>>>  $logsByInspection
     * @param  array<string, string>  $itemColumns  target column => sheet column
     * @param  array<string, string>  $logColumns  target column => sheet column
     * @return array{plans: list<Plan>, open: int, invalid: int, unresolved_equipment: int}
     */
    private function plan(array $inspections, string $idColumn, string $numberColumn, string $statusColumn, string $equipmentColumn, array $lookups, callable $headerAttributes, array $itemsByInspection, array $logsByInspection, array $itemColumns, array $logColumns): array
    {
        $plans = [];
        $open = 0;
        $invalid = 0;
        $unresolved = 0;
        $seen = [];

        foreach ($inspections as $row) {
            $statusId = $row[$statusColumn] === null ? null : strtolower($row[$statusColumn]);

            if (! in_array($statusId, self::CLOSED_STATUS_IDS, true)) {
                $open++;

                continue;
            }

            $number = $row[$numberColumn];

            if ($number === null || isset($seen[$number])) {
                $invalid++;

                continue;
            }

            $seen[$number] = true;
            $sheetId = $row[$idColumn];

            $equipment = ['prc' => null, 'name' => null];
            $equipmentId = $row[$equipmentColumn];

            if ($equipmentId !== null) {
                $equipment = is_numeric($equipmentId) ? ($lookups['equipment'][(int) $equipmentId] ?? null) : null;

                if ($equipment === null) {
                    $equipment = ['prc' => null, 'name' => null];
                    $unresolved++;
                }
            }

            $logs = array_map(
                fn (array $log): array => [
                    'action' => $log[$logColumns['action']],
                    'status' => $log[$logColumns['status']],
                    'remarks' => $log[$logColumns['remarks']],
                    'performed_by' => $log[$logColumns['performed_by']],
                    'logged_at' => $this->formatDate($log[$logColumns['logged_at']]),
                ],
                $logsByInspection[$sheetId] ?? [],
            );

            usort($logs, fn (array $a, array $b): int => strcmp((string) $a['logged_at'], (string) $b['logged_at']));

            $items = array_values(array_filter(array_map(
                fn (array $item): array => [
                    'task' => $item[$itemColumns['task']],
                    'result' => $this->normalizeResult($item[$itemColumns['result']]),
                    'remarks' => $item[$itemColumns['remarks']],
                ],
                $itemsByInspection[$sheetId] ?? [],
            ), fn (array $item): bool => $item['task'] !== null || $item['result'] !== null));

            $plans[] = [
                'inspection_no' => $number,
                'attributes' => [
                    'inspection_no' => $number,
                    'status' => $lookups['statuses'][$statusId] ?? self::FALLBACK_STATUS_TEXT[$statusId],
                    ...$headerAttributes($row, $equipment, $logs),
                ],
                'items' => $items,
                'logs' => $logs,
            ];
        }

        return ['plans' => $plans, 'open' => $open, 'invalid' => $invalid, 'unresolved_equipment' => $unresolved];
    }

    /**
     * @param  class-string<Model>  $model
     * @param  class-string<Model>  $itemModel
     * @param  class-string<Model>  $logModel
     * @param  list<Plan>  $plans
     * @return array{inserted: int, skipped: int, failed: int}
     */
    private function persist(string $model, string $itemModel, string $logModel, string $foreignKey, array $plans): array
    {
        $new = $this->onlyNew($model, $plans);
        $inserted = 0;
        $failed = 0;

        foreach ($new as $plan) {
            try {
                DB::transaction(function () use ($model, $itemModel, $logModel, $foreignKey, $plan): void {
                    $inspection = $model::query()->create($plan['attributes']);

                    foreach ([[$itemModel, $plan['items']], [$logModel, $plan['logs']]] as [$childModel, $rows]) {
                        if ($rows !== []) {
                            $childModel::query()->insert(array_map(fn (array $row): array => $row + [$foreignKey => $inspection->getKey()], $rows));
                        }
                    }
                });

                $inserted++;
            } catch (Throwable $exception) {
                report($exception);
                $failed++;
            }
        }

        return ['inserted' => $inserted, 'skipped' => count($plans) - count($new), 'failed' => $failed];
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     * @return array<string, list<array<string, string|null>>>
     */
    private function groupBy(array $rows, string $column): array
    {
        $groups = [];

        foreach ($rows as $row) {
            if ($row[$column] !== null) {
                $groups[$row[$column]][] = $row;
            }
        }

        return $groups;
    }

    /**
     * @param  array<string, string>  $map
     */
    private function lookup(array $map, ?string $key): ?string
    {
        return $key === null ? null : ($map[$key] ?? null);
    }

    private function formatDate(?string $value): ?string
    {
        return self::parseSheetDate($value)?->format('Y-m-d H:i:s');
    }

    /**
     * @param  list<array<string, mixed>>  $logs
     */
    private function lastLogTime(array $logs): ?string
    {
        $times = array_values(array_filter(array_column($logs, 'logged_at')));

        return $times === [] ? null : max($times);
    }

    /**
     * "a@x.com , b@x.com" becomes "a@x.com, b@x.com".
     */
    private function normalizeList(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $value)), fn (string $part): bool => $part !== ''));

        return $parts === [] ? null : implode(', ', $parts);
    }

    private function normalizeResult(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return match (strtolower($value)) {
            'passed' => 'Passed',
            'failed' => 'Failed',
            'n/a', 'na' => 'N/A',
            default => $value,
        };
    }
}

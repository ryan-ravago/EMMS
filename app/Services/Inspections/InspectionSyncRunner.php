<?php

namespace App\Services\Inspections;

use App\Models\AimsInspection;
use App\Models\MaintenanceInspection;
use App\Models\SyncAuditLog;
use App\Services\Google\GoogleSheetReader;
use App\Services\Inspections\AppSheetInspectionSync as Sync;
use InvalidArgumentException;
use Throwable;

/**
 * Runs one inspection sync (maintenance or AIMS) end to end: read the AppSheet tabs, keep the closed inspections
 * and import the new ones. Used by the scheduled command and by the sync manager pages.
 */
class InspectionSyncRunner
{
    public const MAINTENANCE = 'maintenance';

    public const AIMS = 'aims';

    public const KINDS = [self::MAINTENANCE, self::AIMS];

    public function __construct(
        private readonly GoogleSheetReader $reader,
        private readonly Sync $sync,
    ) {}

    /**
     * @param  'maintenance'|'aims'  $kind
     * @return array{sheet: string, rows: int, open: int, closed: int, skipped: int, imported: int, unresolved_equipment: int, failed: int}
     *
     * @throws Throwable when the sheet cannot be read; a failed audit row is written first
     */
    public function run(string $kind, bool $dryRun = false): array
    {
        if (! in_array($kind, self::KINDS, true)) {
            throw new InvalidArgumentException("Unknown inspection sync kind [{$kind}].");
        }

        $sheet = $kind === self::MAINTENANCE ? 'insps' : 'ainsps';

        try {
            $result = $kind === self::MAINTENANCE ? $this->maintenance($dryRun) : $this->aims($dryRun);
        } catch (Throwable $exception) {
            $this->audit($sheet, 0, null, 'failed', $exception->getMessage(), $dryRun);

            throw $exception;
        }

        $this->audit(
            $sheet,
            $result['imported'],
            $result['rows'],
            $result['failed'] > 0 ? 'failed' : 'success',
            $result['failed'] > 0 ? "{$result['failed']} inspection(s) could not be saved; see the log." : null,
            $dryRun,
        );

        return ['sheet' => $sheet] + $result;
    }

    /**
     * @return array{rows: int, open: int, closed: int, skipped: int, imported: int, unresolved_equipment: int, failed: int}
     */
    private function maintenance(bool $dryRun): array
    {
        $inspections = $this->reader->rows('insps', Sync::MAINTENANCE_COLUMNS);
        $items = $this->reader->rows('insp_items', Sync::MAINTENANCE_ITEM_COLUMNS);
        $logs = $this->reader->rows('insp_logs', Sync::MAINTENANCE_LOG_COLUMNS);

        $lookups = $this->sync->buildLookups(
            $this->reader->rows('statuses', Sync::STATUS_COLUMNS),
            $this->reader->rows('actions', Sync::ACTION_COLUMNS),
            $this->reader->rows('checklists', Sync::CHECKLIST_COLUMNS),
            $this->sync->departmentLookup(),
            $this->sync->equipmentLookup(array_column($inspections, 'insp_eqm_id')),
        );

        $planned = $this->sync->planMaintenance($inspections, $items, $logs, $lookups);

        return $this->finish(MaintenanceInspection::class, $planned, count($inspections), $dryRun, fn (array $plans): array => $this->sync->persistMaintenance($plans));
    }

    /**
     * @return array{rows: int, open: int, closed: int, skipped: int, imported: int, unresolved_equipment: int, failed: int}
     */
    private function aims(bool $dryRun): array
    {
        $inspections = $this->reader->rows('ainsps', Sync::AIMS_COLUMNS);
        $items = $this->reader->rows('ainsp_items', Sync::AIMS_ITEM_COLUMNS);
        $logs = $this->reader->rows('ainsp_logs', Sync::AIMS_LOG_COLUMNS);

        $lookups = $this->sync->buildLookups(
            $this->reader->rows('statuses', Sync::STATUS_COLUMNS),
            $this->reader->rows('actions', Sync::ACTION_COLUMNS),
            [],
            [],
            $this->sync->equipmentLookup(array_column($inspections, 'ainsp_eqm_id')),
        );

        $planned = $this->sync->planAims($inspections, $items, $logs, $lookups);

        return $this->finish(AimsInspection::class, $planned, count($inspections), $dryRun, fn (array $plans): array => $this->sync->persistAims($plans));
    }

    /**
     * @param  class-string<MaintenanceInspection|AimsInspection>  $model
     * @param  array{plans: list<array<string, mixed>>, open: int, invalid: int, unresolved_equipment: int}  $planned
     * @param  callable(list<array<string, mixed>>): array{inserted: int, skipped: int, failed: int}  $persist
     * @return array{rows: int, open: int, closed: int, skipped: int, imported: int, unresolved_equipment: int, failed: int}
     */
    private function finish(string $model, array $planned, int $rows, bool $dryRun, callable $persist): array
    {
        $closed = count($planned['plans']);

        if ($dryRun) {
            $new = count($this->sync->onlyNew($model, $planned['plans']));
            $outcome = ['inserted' => $new, 'skipped' => $closed - $new, 'failed' => 0];
        } else {
            $outcome = $persist($planned['plans']);
        }

        return [
            'rows' => $rows,
            'open' => $planned['open'],
            'closed' => $closed,
            'skipped' => $outcome['skipped'],
            'imported' => $outcome['inserted'],
            'unresolved_equipment' => $planned['unresolved_equipment'],
            'failed' => $outcome['failed'],
        ];
    }

    private function audit(string $sheet, int $synced, ?int $lastRow, string $status, ?string $error, bool $dryRun): void
    {
        if ($dryRun) {
            return;
        }

        SyncAuditLog::query()->create([
            'sheet_name' => $sheet,
            'synced_rows' => $synced,
            'last_row_processed' => $lastRow,
            'status' => $status,
            'error_message' => $error,
            'synced_at' => now(),
        ]);
    }
}

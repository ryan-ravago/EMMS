<?php

namespace App\Console\Commands;

use App\Models\AimsInspection;
use App\Models\MaintenanceInspection;
use App\Models\SyncAuditLog;
use App\Services\Google\GoogleSheetReader;
use App\Services\Inspections\AppSheetInspectionSync as Sync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncInspections extends Command
{
    protected $signature = 'inspections:sync
        {--dry-run : Show what would be imported without writing anything}';

    protected $description = 'Copy closed (Completed / Cancelled) maintenance and AIMS inspections from the AppSheet Google Sheet into plain-text tables';

    public function handle(GoogleSheetReader $reader, Sync $sync): int
    {
        $dryRun = (bool) $this->option('dry-run');

        try {
            $maintenance = [$reader->rows('insps', Sync::MAINTENANCE_COLUMNS), $reader->rows('insp_items', Sync::MAINTENANCE_ITEM_COLUMNS), $reader->rows('insp_logs', Sync::MAINTENANCE_LOG_COLUMNS)];
            $aims = [$reader->rows('ainsps', Sync::AIMS_COLUMNS), $reader->rows('ainsp_items', Sync::AIMS_ITEM_COLUMNS), $reader->rows('ainsp_logs', Sync::AIMS_LOG_COLUMNS)];

            $lookups = $sync->buildLookups(
                $reader->rows('statuses', Sync::STATUS_COLUMNS),
                $reader->rows('actions', Sync::ACTION_COLUMNS),
                $reader->rows('checklists', Sync::CHECKLIST_COLUMNS),
                $sync->departmentLookup(),
                $sync->equipmentLookup([...array_column($maintenance[0], 'insp_eqm_id'), ...array_column($aims[0], 'ainsp_eqm_id')]),
            );

            $results = [
                'insps' => [MaintenanceInspection::class, $sync->planMaintenance(...[...$maintenance, $lookups]), 'persistMaintenance', count($maintenance[0])],
                'ainsps' => [AimsInspection::class, $sync->planAims(...[...$aims, $lookups]), 'persistAims', count($aims[0])],
            ];
        } catch (Throwable $exception) {
            Log::error('Inspection sync failed', ['message' => $exception->getMessage()]);

            foreach (['insps', 'ainsps'] as $sheet) {
                $this->audit($sheet, 0, null, 'failed', $exception->getMessage(), $dryRun);
            }

            $this->error('Sync failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $rows = [];

        foreach ($results as $sheet => [$model, $planned, $persist, $sheetRows]) {
            $new = count($sync->onlyNew($model, $planned['plans']));
            $outcome = $dryRun ? ['inserted' => $new, 'skipped' => count($planned['plans']) - $new, 'failed' => 0] : $sync->{$persist}($planned['plans']);

            $rows[] = [
                $sheet,
                $sheetRows,
                $planned['open'],
                count($planned['plans']),
                $outcome['skipped'],
                $outcome['inserted'],
                $planned['unresolved_equipment'],
                $outcome['failed'],
            ];

            $this->audit($sheet, $outcome['inserted'], $sheetRows, $outcome['failed'] > 0 ? 'failed' : 'success', $outcome['failed'] > 0 ? "{$outcome['failed']} inspection(s) could not be saved; see the log." : null, $dryRun);
        }

        $this->table(['Sheet', 'Rows read', 'Still open', 'Closed', 'Already imported', $dryRun ? 'Would import' : 'Imported', 'Equipment not found', 'Failed'], $rows);
        $this->info($dryRun ? 'Dry run - nothing was written.' : 'Sync complete.');

        return collect($rows)->sum(7) > 0 ? self::FAILURE : self::SUCCESS;
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

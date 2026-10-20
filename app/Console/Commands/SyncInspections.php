<?php

namespace App\Console\Commands;

use App\Services\Inspections\InspectionSyncRunner;
use App\Support\Activity\ActivityLogging;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncInspections extends Command
{
    protected $signature = 'inspections:sync
        {--only= : Sync only "maintenance" or "aims" (default: both)}
        {--manual : Record the run as a manual one instead of a scheduled one}
        {--dry-run : Show what would be imported without writing anything}';

    protected $description = 'Copy closed (Completed / Cancelled) maintenance and AIMS inspections from the AppSheet Google Sheet into plain-text tables';

    public function handle(InspectionSyncRunner $runner): int
    {
        $only = $this->option('only');

        if ($only !== null && ! in_array($only, InspectionSyncRunner::KINDS, true)) {
            $this->error('--only must be "maintenance" or "aims".');

            return self::INVALID;
        }

        $kinds = $only === null ? InspectionSyncRunner::KINDS : [$only];
        $dryRun = (bool) $this->option('dry-run');
        $trigger = $this->option('manual') ? 'Manual' : 'Scheduled';

        $rows = [];
        $failed = false;

        foreach ($kinds as $kind) {
            $startedAt = microtime(true);

            try {
                $result = $runner->run($kind, $dryRun);
            } catch (Throwable $exception) {
                Log::error('Inspection sync failed', ['kind' => $kind, 'message' => $exception->getMessage()]);

                if (! $dryRun) {
                    ActivityLogging::inspectionSync($kind, $trigger, 'failed', error: $exception->getMessage(), startedAt: $startedAt);
                }

                $this->error("Sync of {$kind} inspections failed: {$exception->getMessage()}");
                $failed = true;

                continue;
            }

            if (! $dryRun) {
                ActivityLogging::inspectionSync($kind, $trigger, $result['failed'] > 0 ? 'failed' : 'success', $result, $result['failed'] > 0 ? "{$result['failed']} inspection(s) could not be saved" : null, $startedAt);
            }

            $failed = $failed || $result['failed'] > 0;

            $rows[] = [$result['sheet'], $result['rows'], $result['open'], $result['closed'], $result['skipped'], $result['imported'], $result['unresolved_equipment'], $result['failed']];
        }

        if ($rows !== []) {
            $this->table(['Sheet', 'Rows read', 'Still open', 'Closed', 'Already imported', $dryRun ? 'Would import' : 'Imported', 'Equipment not found', 'Failed'], $rows);
            $this->info($dryRun ? 'Dry run - nothing was written.' : 'Sync complete.');
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}

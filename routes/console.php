<?php

use App\Console\Commands\CheckDueMaintenanceCommand;
use App\Console\Commands\SendOverdueMaintenanceTaskNotifications;
use App\Console\Commands\SyncGoogleWorkOrderReplies;
use App\Console\Commands\SyncInspections;
use App\Jobs\ProcessDueDateChecks;
use App\Models\Activity;
use App\Models\AimsInspectionLog;
use App\Models\MaintenanceInspectionLog;
use App\Models\RfidRawLog;
use App\Models\RfidScannerStatusLog;
use App\Services\Inspections\InspectionSyncRunner;
use App\Support\InspectionSyncSettings;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::job(new ProcessDueDateChecks)
//     ->everyMinute()         // runs every minute, job itself filters by clt_schedule_time
//     ->withoutOverlapping()
//     ->onFailure(function () {
//         Log::error('[DueDateChecks] Job failed');
//     });

// Schedule::command(SendOverdueMaintenanceTaskNotifications::class)
//     ->daily()
//     ->withoutOverlapping();

Schedule::command(SyncGoogleWorkOrderReplies::class)
    ->everyMinute()
    ->withoutOverlapping();

// Copy closed (Completed / Cancelled) AppSheet inspections into the maintenance_inspections / aims_inspections
// tables. Each has its own daily time, changed from its sync manager page (defaults: 01:00 and 01:15).
Schedule::command(SyncInspections::class, ['--only' => InspectionSyncRunner::MAINTENANCE])
    ->dailyAt(InspectionSyncSettings::time(InspectionSyncRunner::MAINTENANCE))
    ->withoutOverlapping();

Schedule::command(SyncInspections::class, ['--only' => InspectionSyncRunner::AIMS])
    ->dailyAt(InspectionSyncSettings::time(InspectionSyncRunner::AIMS))
    ->withoutOverlapping();

// Log retention: once a day, delete log rows older than config('log_retention.days') days (default 30).
// Only these tables are pruned (the activity log, which also holds the inspection sync history,
// the maintenance and AIMS inspection logs, and the raw RFID reader log and scanner status history, which keep their own
// rfid.raw_log_retention_days and rfid.scanner_log_retention_days). Naming the models keeps any other model that later becomes
// prunable out of this run.
Schedule::command('model:prune', [
    '--model' => [Activity::class, MaintenanceInspectionLog::class, AimsInspectionLog::class, RfidRawLog::class, RfidScannerStatusLog::class],
    '--chunk' => config('log_retention.chunk'),
])
    ->dailyAt(config('log_retention.run_at'))
    ->withoutOverlapping()
    ->name('log-retention');

Schedule::command(CheckDueMaintenanceCommand::class)
    ->daily()
    ->at('08:00');

// Tell IT when the RFID reader server stops getting scans to EMMS (only sends when RFID_ALERTS_ENABLED is on).
Schedule::command('rfid:check-health')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->onOneServer();

// Tell IT when a gate scanner is reported inactive, or the reader server stops reporting its status (reports come
// about every 5 minutes; only sends when RFID_ALERTS_ENABLED is on).
Schedule::command('rfid:check-scanners')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

// Safety net for the job dispatched after every reader request: move any raw reader reads that are still waiting
// (queue worker down, job failed) into the tag logs and trips.
Schedule::command('rfid:process-raw-logs')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

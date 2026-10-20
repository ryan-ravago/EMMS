<?php

use App\Console\Commands\CheckDueMaintenanceCommand;
use App\Console\Commands\SendOverdueMaintenanceTaskNotifications;
use App\Console\Commands\SyncGoogleWorkOrderReplies;
use App\Console\Commands\SyncInspections;
use App\Jobs\ProcessDueDateChecks;
use App\Jobs\SyncEquipmentFromSap;
use App\Models\Activity;
use App\Models\AimsInspectionLog;
use App\Models\AppSetting;
use App\Models\MaintenanceInspectionLog;
use App\Services\Inspections\InspectionSyncRunner;
use App\Support\Activity\ActivityLogging;
use App\Support\InspectionSyncSettings;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if (! app()->runningUnitTests() && Schema::hasTable((new AppSetting)->getTable())) {
    $syncTime = AppSetting::where('key', '=', 'sap_sync_time', 'and')->value('value');

    if ($syncTime) {
        Schedule::job(new SyncEquipmentFromSap)
            ->dailyAt($syncTime)
            ->withoutOverlapping()
            // Logged when the schedule fires, so a day with a start row but no result row
            // means the queue worker never picked the job up.
            ->before(fn () => ActivityLogging::sapSync('Scheduled', 'queued'));
    }
}

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
// Only these three tables are pruned (the activity log, which also holds the SAP and inspection sync history,
// and the maintenance and AIMS inspection logs). Naming the models keeps any other model that later becomes
// prunable out of this run.
Schedule::command('model:prune', [
    '--model' => [Activity::class, MaintenanceInspectionLog::class, AimsInspectionLog::class],
    '--chunk' => config('log_retention.chunk'),
])
    ->dailyAt(config('log_retention.run_at'))
    ->withoutOverlapping()
    ->name('log-retention');

Schedule::command(CheckDueMaintenanceCommand::class)
    ->daily()
    ->at('08:00');

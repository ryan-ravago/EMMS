<?php

use App\Console\Commands\CheckDueMaintenanceCommand;
use App\Console\Commands\SendOverdueMaintenanceTaskNotifications;
use App\Console\Commands\SyncGoogleSheetInspections;
use App\Console\Commands\SyncGoogleWorkOrderReplies;
use App\Jobs\ProcessDueDateChecks;
use App\Jobs\SyncEquipmentFromSap;
use App\Models\AppSetting;
use App\Support\Activity\ActivityLogging;
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

// Schedule::command(SyncGoogleSheetInspections::class)
//     ->everyMinute()
//     ->withoutOverlapping();

Schedule::command(CheckDueMaintenanceCommand::class)
    ->daily()
    ->at('08:00');

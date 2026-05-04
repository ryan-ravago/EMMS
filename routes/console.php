<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\SyncEquipmentFromSap;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$syncTime = AppSetting::where('key', 'sap_sync_time')->value('value');

Schedule::job(new SyncEquipmentFromSap)
    ->dailyAt($syncTime)
    ->withoutOverlapping();

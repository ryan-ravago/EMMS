<?php

namespace App\Support;

use App\Models\AppSetting;
use App\Services\Inspections\InspectionSyncRunner;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * The daily run time of each inspection sync, kept in app_settings so it can be changed from the sync manager pages.
 */
class InspectionSyncSettings
{
    private const KEYS = [
        InspectionSyncRunner::MAINTENANCE => 'maintenance_inspection_sync_time',
        InspectionSyncRunner::AIMS => 'aims_inspection_sync_time',
    ];

    /** Staggered so the two syncs do not hit Google at the same moment. */
    private const DEFAULT_TIMES = [
        InspectionSyncRunner::MAINTENANCE => '01:00',
        InspectionSyncRunner::AIMS => '01:15',
    ];

    public static function key(string $kind): string
    {
        return self::KEYS[$kind];
    }

    public static function defaultTime(string $kind): string
    {
        return self::DEFAULT_TIMES[$kind];
    }

    /**
     * The saved "HH:MM" time, or the default when nothing is saved (or the setting cannot be read yet,
     * for example while the app is booting before its tables exist).
     */
    public static function time(string $kind): string
    {
        try {
            if (! Schema::hasTable((new AppSetting)->getTable())) {
                return self::defaultTime($kind);
            }

            $saved = AppSetting::get(self::key($kind));
        } catch (Throwable) {
            return self::defaultTime($kind);
        }

        return is_string($saved) && preg_match('/^\d{1,2}:\d{2}/', $saved) === 1
            ? substr($saved, 0, 5)
            : self::defaultTime($kind);
    }
}

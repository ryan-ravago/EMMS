<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Records create / update / delete of a model in the activity log, with old and new values.
 * The readable description, record name and "triggered by" info are added centrally by
 * App\Support\Activity\ActivityLogging.
 *
 * Keep secrets and noisy columns out of the log with:  protected array $activityExclude = ['column'];
 */
trait LogsChanges
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept(array_merge(
                ['created_at', 'updated_at', 'remember_token', 'password'],
                property_exists($this, 'activityExclude') ? $this->activityExclude : [],
            ))
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

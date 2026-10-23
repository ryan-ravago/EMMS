<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Spatie\Activitylog\Models\Activity as BaseActivity;

/**
 * The activity log row (set as the package's activity model in config/activitylog.php). It adds the retention
 * rule: rows older than the retention period are removed by the scheduled "model:prune" run.
 *
 * This also removes the inspection sync history, which is stored as activity rows.
 */
class Activity extends BaseActivity
{
    use MassPrunable;

    /**
     * Rows older than the retention period, by when they were logged (created_at).
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()->where('created_at', '<', now()->subDays((int) config('log_retention.days')));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MaintenanceTask extends Model
{
    use LogsActivity;

    protected $table = 'maintenance_tasks';

    protected $primaryKey = 'mt_id';

    protected $fillable = [
        'mt_batch_id',
        'mt_eqm_id',
        'mt_eqm_log',
        'mt_dep_id',
        'mt_task_id',
        'mt_task_log',
        'mt_status_id',
        'mt_due_dt',
        'mt_remarks',
        'mt_scheduled_dt',
        'mt_closed_dt',
        'mt_by',
        'mt_dt',
    ];

    public $timestamps = false;

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'mt_dep_id', 'dep_id');
    }

    public function equipmentUnit(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'mt_eqm_id', 'eqm_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'mt_task_id', 'task_id');
    }

    public function maintenanceTaskLogs(): HasMany
    {
        return $this->hasMany(MaintenanceTaskLog::class, 'mtl_mt_id', 'mt_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'mt_status_id', 'status_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'wo_mt_id', 'mt_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'mt_by', 'user_id', 'user_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('Maintenance')
            ->setDescriptionForEvent(fn (string $eventName) => "Maintenance Task has been {$eventName}")
            ->dontSubmitEmptyLogs();
    }
}

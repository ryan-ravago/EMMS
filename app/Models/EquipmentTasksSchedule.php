<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentTasksSchedule extends Model
{
    protected $table = 'equipment_tasks_schedules';
    protected $primaryKey = 'ets_id';
    public $timestamps = false;

    protected $fillable = [
        'ets_dep_id',
        'ets_eqm_id',
        'ets_task_id',
        'ets_sort_order',
        'ets_itrv_years',
        'ets_itrv_months',
        'ets_itrv_weeks',
        'ets_itrv_days',
        'ets_sched_time',
        'ets_due_effectivity_dt',
        'ets_due_dt',
        'ets_assigned_by',
        'ets_assigned_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (EquipmentTasksSchedule $schedule) {
            if (!$schedule->ets_assigned_by) {
                $schedule->ets_assigned_by = auth()->id();
            }
            if (!$schedule->ets_assigned_at) {
                $schedule->ets_assigned_at = now();
            }
        });

        static::updating(function (EquipmentTasksSchedule $schedule) {
            $schedule->ets_assigned_by = auth()->id();
            $schedule->ets_assigned_at = now();
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'ets_dep_id', 'dep_id');
    }

    public function equipmentUnit(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'ets_eqm_id', 'eqm_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'ets_task_id', 'task_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'ets_assigned_by', 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    protected $table = 'tasks';

    protected $primaryKey = 'task_id';

    public $timestamps = false;

    protected $fillable = [
        'task_name',
        'task_dep_id',
        'task_tut_id',
        'task_created_by',
        'task_created_at',
        'task_last_updated_by',
        'task_last_updated_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            $now = now();

            if (! $task->task_created_by) {
                $task->task_created_by = Auth::id();
            }
            if (! $task->task_created_at) {
                $task->task_created_at = $now;
            }
            if (! $task->task_dep_id) {
                $task->task_dep_id = Auth::user()?->user_dep_id; // ← current user's dept
            }
            if (! $task->task_last_updated_by) {
                $task->task_last_updated_by = Auth::id();
            }
            if (! $task->task_last_updated_at) {
                $task->task_last_updated_at = $now;
            }
        });

        static::updating(function (Task $task) {
            $now = now();

            $task->task_last_updated_by = Auth::id();
            $task->task_last_updated_at = $now;
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'task_dep_id', 'dep_id');
    }

    public function usageType(): BelongsTo
    {
        return $this->belongsTo(TaskUsageType::class, 'task_tut_id', 'tut_id');
    }

    public function taskUsageType(): BelongsTo
    {
        return $this->belongsTo(TaskUsageType::class, 'task_tut_id', 'tut_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'task_created_by', 'user_id');
    }

    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'task_last_updated_by', 'user_id');
    }

    public function equipmentUnitsForTemplate(): BelongsToMany
    {
        return $this->equipmentTypesForTemplate();
    }

    public function equipmentTypesForTemplate(): BelongsToMany
    {
        return $this->belongsToMany(EquipmentType::class, 'equipment_type_task_checklist_template', 'etct_task_id', 'etct_eqmt_id');
    }

    public function equipmentUnitsForSchedule(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'equipment_tasks_schedules', 'ets_task_id', 'ets_eqm_id')
            ->withPivot([
                'ets_id',
                'ets_dep_id',
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
            ]);
    }
}

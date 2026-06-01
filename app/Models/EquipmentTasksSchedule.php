<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        'ets_last_assigned_by',
        'ets__last_assigned_at'
    ];

    protected static function booted(): void
    {
        static::creating(function (EquipmentTasksSchedule $schedule) {
            $now = now();

            // Auto-fill department from logged-in user
            if (!$schedule->ets_dep_id) {
                $schedule->ets_dep_id = auth()->user()->user_dep_id;
            }

            if (!$schedule->ets_assigned_by) {
                $schedule->ets_assigned_by = auth()->id();
            }
            if (!$schedule->ets_assigned_at) {
                $schedule->ets_assigned_at = $now;
            }

            // Only calculate due date if no open maintenance task exists for same equipment + task
            $hasOpenTask = DB::table('maintenance_tasks')
                ->where('mt_eqm_id', $schedule->ets_eqm_id)
                ->where('mt_task_id', $schedule->ets_task_id)
                ->whereIn('mt_status_id', ['pnd', 'snz', 'inprog'])
                ->exists();

            if (!$hasOpenTask) {
                // Auto-calculate due date from interval
                $schedule->ets_due_dt = Carbon::parse($schedule->ets_due_effectivity_dt)
                    ->addYears($schedule->ets_itrv_years ?? 0)
                    ->addMonths($schedule->ets_itrv_months ?? 0)
                    ->addWeeks($schedule->ets_itrv_weeks ?? 0)
                    ->addDays($schedule->ets_itrv_days ?? 0)
                    ->setTimeFromTimeString($schedule->ets_sched_time ?? '00:00:00');
            }
        });

        static::updating(function (EquipmentTasksSchedule $schedule) {
            $now = now();

            $schedule->ets_last_assigned_by = auth()->id();
            $schedule->ets_last_assigned_at = $now;

            // Only recalculate due date if no open maintenance task exists for this specific combination
            $hasOpenTask = DB::table('maintenance_tasks')
                ->where('mt_dep_id', $schedule->ets_dep_id)   // Matching Department ID
                ->where('mt_task_id', $schedule->ets_task_id) // Matching Task ID
                ->where('mt_eqm_id', $schedule->ets_eqm_id)   // Matching Equipment ID
                ->whereIn('mt_status_id', ['pnd', 'snz', 'inprog']) // Active "open" states
                ->exists();

            if (!$hasOpenTask) {
                $schedule->ets_due_dt = Carbon::parse($schedule->ets_due_effectivity_dt)
                    ->addYears($schedule->ets_itrv_years ?? 0)
                    ->addMonths($schedule->ets_itrv_months ?? 0)
                    ->addWeeks($schedule->ets_itrv_weeks ?? 0)
                    ->addDays($schedule->ets_itrv_days ?? 0)
                    ->setTimeFromTimeString($schedule->ets_sched_time ?? '00:00:00');
            }
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

    public function lastAssignedBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'ets_last_assigned_by', 'user_id');
    }
}

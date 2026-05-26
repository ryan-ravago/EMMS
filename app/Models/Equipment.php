<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $table = 'equipment_units';
    protected $primaryKey = 'eqm_id';

    protected $fillable = [
        'eqmc_name',
        'eqm_eqmm_id',
        'eqm_name',
        'eqm_vin',
        'eqm_plate_num',
        'eqm_prc_code',
        'eqm_serial_num',
        'eqm_engine',
        'eqm_is_active',
        'eqm_updated_at',
    ];

    public $timestamps = false;

    public function model()
    {
        return $this->belongsTo(EquipmentModel::class, 'eqm_eqmm_id', 'eqmm_id');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'equipment_task_checklist_template', 'etct_eqm_id', 'etct_task_id');
    }

    public function scheduledTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'equipment_tasks_schedules', 'ets_eqm_id', 'ets_task_id')
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
                'ets_assigned_at'
            ]);
    }

    public function equipmentTasksSchedules(): HasMany
    {
        // return $this->hasMany(EquipmentTasksSchedule::class, 'ets_eqm_id', 'eqm_id');
        return $this->hasMany(EquipmentTasksSchedule::class, 'ets_eqm_id', 'eqm_id')
            ->when(
                auth()->check() && !auth()->user()->hasRole('super_admin'),
                fn($query) => $query->where('ets_dep_id', auth()->user()->user_dep_id)
            );
    }

    // protected static function booted(): void
    // {
    //     $bust = fn() => cache()->forget('equipment_count');

    //     static::created($bust);
    //     static::updated($bust);
    //     static::deleted($bust);
    // }
}

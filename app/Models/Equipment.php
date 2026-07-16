<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Equipment extends Model
{
    use LogsActivity;

    protected $table = 'equipment_units';

    protected $primaryKey = 'eqm_id';

    protected $fillable = [
        'eqmc_name',
        'eqm_eqmm_id',
        'eqm_brand_id',
        'eqm_eqmt_id',
        'eqm_chassis_no',
        'eqm_date_purchased',
        'eqm_name',
        'eqm_pm_itrv_type',
        'eqm_pm_itrv_value',
        'eqm_pm_itrv_start_date',
        'eqm_next_pm_due_at',
        'eqm_last_pm_notified_at',
        'eqm_vin',
        'eqm_plate_num',
        'eqm_prc_code',
        'eqm_serial_num',
        'eqm_engine',
        'eqm_is_active',
        'eqm_updated_at',
    ];

    public $timestamps = false;

    // ✅ With casts - Clean and safe
    protected $casts = [
        'eqm_pm_itrv_start_date' => 'date',
        'eqm_next_pm_due_at' => 'date',
        'eqm_last_pm_notified_at' => 'date',
    ];

    // public function type(): HasOneThrough
    // {
    //     return $this->hasOneThrough(
    //         EquipmentType::class,
    //         EquipmentModel::class,
    //         'eqmm_id',
    //         'eqmt_id',
    //         'eqm_eqmm_id',
    //         'eqmm_eqmt_id'
    //     );
    // }

    public function type(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class, 'eqm_eqmt_id', 'eqmt_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(EquipmentBrand::class, 'eqm_brand_id', 'eqmb_id');
    }

    public function equipmentModel()
    {
        return $this->belongsTo(EquipmentModel::class, 'eqm_eqmm_id', 'eqmm_id');
    }

    // public function tasks(): BelongsToMany
    // {
    //     return $this->belongsToMany(Task::class, 'equipment_type_task_checklist_template', 'etct_eqmt_id', 'etct_task_id');
    // }

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
                'ets_assigned_at',
            ]);
    }

    public function equipmentTasksSchedules(): HasMany
    {
        // return $this->hasMany(EquipmentTasksSchedule::class, 'ets_eqm_id', 'eqm_id');
        return $this->hasMany(EquipmentTasksSchedule::class, 'ets_eqm_id', 'eqm_id')
            ->when(
                Auth::check() && ! Auth::user()->hasRole('super_admin'),
                fn($query) => $query->where('ets_dep_id', Auth::user()->user_dep_id)
            );
    }

    public function equipmentTaskChecklistTemplates(): HasManyThrough
    {
        return $this->hasManyThrough(
            EquipmentTaskChecklistTemplate::class,
            EquipmentModel::class,
            'eqmm_id',
            'etct_eqmt_id',
            'eqm_eqmm_id',
            'eqmm_eqmt_id'
        );
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'ins_eqm_id', 'eqm_id');
    }

    public function insps(): HasMany
    {
        return $this->hasMany(Insp::class, 'insp_eqm_id', 'eqm_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'wo_eqm_id', 'eqm_id');
    }

    public function maintenanceTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class, 'mt_eqm_id', 'eqm_id');
    }

    public function calculateNextDueDate(): ?Carbon
    {
        if (!$this->eqm_pm_itrv_start_date || !$this->eqm_pm_itrv_type || !$this->eqm_pm_itrv_value) {
            return null;
        }

        // Use last notified date if available, otherwise use start date
        $baseDate = $this->eqm_last_pm_notified_at
            ? Carbon::parse($this->eqm_last_pm_notified_at)
            : Carbon::parse($this->eqm_pm_itrv_start_date);

        if ($this->eqm_pm_itrv_type === 'monthly') {
            return $baseDate->addMonths($this->eqm_pm_itrv_value);
        }

        return $baseDate->addWeeks($this->eqm_pm_itrv_value);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['eqm_name', 'eqm_is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

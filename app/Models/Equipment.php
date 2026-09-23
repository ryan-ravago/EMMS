<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\EquipmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\Traits\LogsActivity;

class Equipment extends Model
{
    /** @use HasFactory<EquipmentFactory> */
    use HasFactory;

    use LogsActivity;

    protected $table = 'equipment_units';

    protected $primaryKey = 'eqm_id';

    protected $fillable = [
        'asset_type_id',
        'parent_id',
        'lifecycle_status_id',
        'location_id',
        'year_model',
        'specifications',

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
        'eqm_is_active' => 'boolean',
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

    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function editLogs(): HasMany
    {
        return $this->hasMany(AssetEditLog::class, 'asset_id', 'eqm_id');
    }

    /**
     * Persist the model inside a transaction so the edit-log write (below) is atomic with the save.
     *
     * @param  array<string, mixed>  $options
     */
    public function save(array $options = []): bool
    {
        if (! $this->exists) {
            return parent::save($options);
        }

        return DB::transaction(fn(): bool => parent::save($options));
    }

    protected static function booted(): void
    {
        // Runs after the UPDATE query and after EquipmentObserver::saving() has
        // mutated fields (parent_id, eqm_eqmt_id, eqm_next_pm_due_at), but before
        // syncOriginal() — so getChanges()/getOriginal() both reflect the true diff.
        static::updated(function (self $equipment): void {
            $diff = collect($equipment->getChanges())
                ->except('eqm_updated_at')
                ->mapWithKeys(fn($new, $field) => [
                    $field => [
                        'old' => $equipment->getOriginal($field),
                        'new' => $new,
                    ],
                ])
                ->toArray();

            if (empty($diff)) {
                return;
            }

            $equipment->editLogs()->create([
                'changes' => $diff,
                'performed_by' => Auth::id(),
                'logged_at' => now(),
            ]);
        });
    }

    protected function specifications(): Attribute
    {
        return Attribute::make(
            set: function (?string $value) {
                $stripped = trim(html_entity_decode(strip_tags($value ?? ''), ENT_QUOTES));

                return $stripped === '' ? null : $value;
            },
        );
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'eqm_id');
    }

    public function accessories(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'eqm_id');
    }

    public function isEquipmentAsset(): bool
    {
        if ($this->relationLoaded('assetType')) {
            return $this->assetType?->isEquipment() === true;
        }

        return (int) $this->asset_type_id === (int) AssetType::equipmentId();
    }

    public function isAccessory(): bool
    {
        if ($this->relationLoaded('assetType')) {
            return $this->assetType?->isAccessory() === true;
        }

        return (int) $this->asset_type_id === (int) AssetType::accessoryId();
    }

    /**
     * @param  Builder<Equipment>  $query
     * @return Builder<Equipment>
     */
    public function scopeEquipmentAssets(Builder $query): Builder
    {
        return $query->whereHas(
            'assetType',
            fn(Builder $assetTypeQuery) => $assetTypeQuery->whereRaw('LOWER(name) = ?', [strtolower(AssetType::EQUIPMENT)])
        );
    }

    /**
     * @param  Builder<Equipment>  $query
     * @return Builder<Equipment>
     */
    public function scopeAccessories(Builder $query): Builder
    {
        return $query->whereHas(
            'assetType',
            fn(Builder $assetTypeQuery) => $assetTypeQuery->whereRaw('LOWER(name) = ?', [strtolower(AssetType::ACCESSORY)])
        );
    }

    public function lifecycleStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'lifecycle_status_id', 'status_id');
    }

    public function lifecycleLogs(): HasMany
    {
        return $this->hasMany(LifecycleLog::class, 'asset_id', 'eqm_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            EquipmentCategory::class,
            'equipment_unit_category',
            'eqm_id',
            'eqmc_id',
            'eqm_id',
            'eqmc_id'
        );
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class, 'eqm_eqmt_id', 'eqmt_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(EquipmentBrand::class, 'eqm_brand_id', 'eqmb_id');
    }

    public function equipmentBrand(): BelongsTo
    {
        return $this->brand();
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
        if (! $this->eqm_pm_itrv_start_date || ! $this->eqm_pm_itrv_type || ! $this->eqm_pm_itrv_value) {
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

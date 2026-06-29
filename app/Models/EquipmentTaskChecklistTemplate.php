<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class EquipmentTaskChecklistTemplate extends Model
{
    protected $table = 'equipment_type_task_checklist_template';

    protected $primaryKey = 'etct_id';

    public $timestamps = false;

    protected $fillable = [
        'etct_dep_id',
        'etct_eqmt_id',
        'etct_task_id',
        'etct_created_by',
        'etct_created_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (EquipmentTaskChecklistTemplate $template) {
            if (! $template->etct_dep_id && Auth::check()) {
                $template->etct_dep_id = Auth::user()->user_dep_id;
            }

            if (! $template->etct_created_by && Auth::check()) {
                $template->etct_created_by = Auth::id();
            }

            if (! $template->etct_created_at) {
                $template->etct_created_at = now();
            }
        });
    }

    public static function forEquipmentId(int|string $equipmentId, ?int $departmentId = null): Collection
    {
        $equipment = Equipment::query()
            ->with('model')
            ->find($equipmentId);

        if (! $equipment?->model?->eqmm_eqmt_id) {
            return collect();
        }

        return static::query()
            ->with('task')
            ->where('etct_eqmt_id', $equipment->model->eqmm_eqmt_id)
            ->when($departmentId, fn(Builder $query) => $query->where('etct_dep_id', $departmentId))
            ->get();
    }

    public static function forEquipment(Equipment $equipment, ?int $departmentId = null): Collection
    {
        $equipment->loadMissing('model');

        if (! $equipment->model?->eqmm_eqmt_id) {
            return collect();
        }

        return static::query()
            ->with('task')
            ->where('etct_eqmt_id', $equipment->model->eqmm_eqmt_id)
            ->when($departmentId, fn(Builder $query) => $query->where('etct_dep_id', $departmentId))
            ->get();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'etct_dep_id', 'dep_id');
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class, 'etct_eqmt_id', 'eqmt_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'etct_task_id', 'task_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'etct_created_by', 'user_id');
    }
}

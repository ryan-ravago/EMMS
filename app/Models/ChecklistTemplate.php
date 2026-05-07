<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistTemplate extends Model
{
    protected $table = 'checklist_templates';
    protected $primaryKey = 'clt_id';

    protected $fillable = [
        'clt_name',
        'clt_cut_id',
        'clt_dep_id',
        'clt_interval_years',
        'clt_interval_months',
        'clt_interval_weeks',
        'clt_interval_days',
        'clt_schedule_time',
        'clt_created_by',
        'clt_created_dt',
        'clt_last_updated_by',
        'clt_last_updated_dt',
    ];

    public $timestamps = false;

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'clt_dep_id', 'dep_id');
    }

    public function checklistUsageType(): BelongsTo
    {
        return $this->belongsTo(ChecklistUsageType::class, 'clt_cut_id', 'cut_id');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class, 'cli_clt_id', 'clt_id');
    }

    public function equipmentUnits(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'equipment_checklist_assignments', 'eca_clt_id', 'eca_eqm_id')
            ->using(EquipmentChecklistAssignment::class)
            ->withPivot('eca_assigned_by', 'eca_assigned_at');
    }

    public function equipment(): BelongsToMany
    {
        return $this->equipmentUnits();
    }

    protected static function booted(): void
    {
        static::deleting(function (ChecklistTemplate $template) {
            $template->checklistItems()->delete();
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EquipmentChecklistAssignment extends Pivot
{
    protected $table = 'equipment_checklist_assignments';
    protected $primaryKey = 'eca_id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'eca_clt_id',
        'eca_eqm_id',
        'eca_due_effectivity_dt',
        'eca_due_dt',
        'eca_assigned_by',
        'eca_assigned_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (EquipmentChecklistAssignment $assignment) {
            $assignment->eca_assigned_by = auth()->id();
            $assignment->eca_assigned_at = now();
        });

        static::updating(function (EquipmentChecklistAssignment $assignment) {
            $assignment->eca_assigned_by = auth()->id();
            $assignment->eca_assigned_at = now();
        });
    }

    public function checklistTemplate(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'eca_clt_id', 'clt_id');
    }

    public function equipmentUnit(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'eca_eqm_id', 'eqm_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'eca_assigned_by', 'user_id');
    }
}

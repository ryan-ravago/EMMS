<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    // Equipment.php
    public function checklistTemplates(): BelongsToMany
    {
        return $this->belongsToMany(ChecklistTemplate::class, 'equipment_checklist_assignments', 'eca_eqm_id', 'eca_clt_id')
            ->using(EquipmentChecklistAssignment::class)
            ->withPivot('eca_assigned_by', 'eca_assigned_at');
    }
}

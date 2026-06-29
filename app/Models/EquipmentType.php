<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentType extends Model
{
    protected $table = 'equipment_types';

    protected $primaryKey = 'eqmt_id';

    protected $fillable = ['eqmt_name'];

    public $timestamps = false;

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'equipment_type_task_checklist_template', 'etct_eqmt_id', 'etct_task_id');
    }

    public function equipmentTaskChecklistTemplates(): HasMany
    {
        return $this->hasMany(EquipmentTaskChecklistTemplate::class, 'etct_eqmt_id', 'eqmt_id');
    }

    public function models(): HasMany
    {
        return $this->hasMany(EquipmentModel::class, 'eqmm_eqmt_id', 'eqmt_id');
    }
}

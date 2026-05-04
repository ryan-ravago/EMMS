<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentType extends Model
{
    protected $table = 'equipment_types';
    protected $primaryKey = 'eqmt_id';

    protected $fillable = ['eqmt_name'];

    public $timestamps = false;

    public function models(): HasMany
    {
        return $this->hasMany(EquipmentModel::class, 'eqmm_eqmt_id', 'eqmt_id');
    }
}

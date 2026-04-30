<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentBrand extends Model
{
    protected $table = 'equipment_brands';
    protected $primaryKey = 'eqmb_id';

    protected $fillable = ['eqmb_name'];

    public $timestamps = false;

    public function models()
    {
        return $this->hasMany(EquipmentModel::class, 'eqmm_eqmb_id', 'eqmb_id');
    }
}

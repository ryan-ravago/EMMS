<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelType extends Model
{
    protected $table = 'fuel_types';
    protected $primaryKey = 'fuel_id';

    protected $fillable = ['fuel_name'];

    public $timestamps = false;

    public function models()
    {
        return $this->hasMany(EquipmentModel::class, 'eqmm_fuel_type', 'fuel_id');
    }
}

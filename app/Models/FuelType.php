<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\LogsChanges;

class FuelType extends Model
{
    use LogsChanges;

    protected $table = 'fuel_types';
    protected $primaryKey = 'fuel_id';

    protected $fillable = ['fuel_name'];

    public $timestamps = false;

    public function models()
    {
        return $this->hasMany(EquipmentModel::class, 'eqmm_fuel_type', 'fuel_id');
    }
}

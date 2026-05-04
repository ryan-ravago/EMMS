<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentModel extends Model
{
    protected $table = 'equipment_models';
    protected $primaryKey = 'eqmm_id';

    protected $fillable = [
        'eqmm_name',
        'eqmm_eqmc_id',
        'eqmm_brand_id',
        'eqmm_fuel_type',
        'eqmm_fuel_type',
        'eqmm_eqmt_id',
        'eqmm_max_capacity_tons',
        'eqmm_max_reach_meters',
    ];

    public $timestamps = false;

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'eqmm_eqmc_id', 'eqmc_id');
    }

    public function brand()
    {
        return $this->belongsTo(EquipmentBrand::class, 'eqmm_brand_id', 'eqmb_id');
    }

    public function fuel_type()
    {
        return $this->belongsTo(FuelType::class, 'eqmm_fuel_type', 'fuel_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class, 'eqmm_eqmt_id', 'eqmt_id');
    }
}

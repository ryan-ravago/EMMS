<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class EquipmentBrand extends Model
{
    protected $table = 'equipment_brands';

    protected $primaryKey = 'eqmb_id';

    protected $fillable = ['eqmb_name'];

    public $timestamps = false;

    public function models()
    {
        return $this->hasMany(EquipmentModel::class, 'eqmm_brand_id', 'eqmb_id');
    }

    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class, 'eqm_brand_id', 'eqmb_id');
    }

    public function equipmentUnits(): HasMany
    {
        return $this->hasMany(Equipment::class, 'eqm_brand_id', 'eqmb_id');
    }

    public function equipmentsThroughModel(): HasManyThrough
    {
        return $this->hasManyThrough(
            Equipment::class,
            EquipmentModel::class,
            'eqmm_brand_id',
            'eqm_eqmm_id',
            'eqmb_id',
            'eqmm_id',
        );
    }
}

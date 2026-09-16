<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class EquipmentModel extends Model
{
    protected $table = 'equipment_models';

    protected $primaryKey = 'eqmm_id';

    protected $fillable = [
        'eqmm_name',
        'eqmm_brand_id',
        'eqmm_fuel_type',
        'eqmm_fuel_type',
        'eqmm_eqmt_id',
        'eqmm_max_capacity_tons',
        'eqmm_max_reach_meters',
    ];

    public $timestamps = false;

    /**
     * Save the model and synchronize the denormalized equipment type atomically.
     *
     * @param  array<string, mixed>  $options
     */
    public function save(array $options = []): bool
    {
        return DB::transaction(function () use ($options): bool {
            $saved = parent::save($options);

            if ($saved && $this->wasChanged('eqmm_eqmt_id')) {
                Equipment::query()
                    ->where('eqm_eqmm_id', $this->getKey())
                    ->update(['eqm_eqmt_id' => $this->eqmm_eqmt_id]);
            }

            return $saved;
        });
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

    public function equipments()
    {
        return $this->hasMany(Equipment::class, 'eqm_eqmm_id', 'eqmm_id');
    }
}

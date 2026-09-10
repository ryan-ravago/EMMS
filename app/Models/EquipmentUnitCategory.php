<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EquipmentUnitCategory extends Pivot
{
    protected $table = 'equipment_unit_category';

    public $timestamps = false;

    protected $fillable = [
        'eqm_id',
        'eqmc_id',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'eqm_id', 'eqm_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'eqmc_id', 'eqmc_id');
    }
}

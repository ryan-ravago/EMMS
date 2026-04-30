<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'eqm_is_active',
        'eqm_updated_at',
    ];

    public $timestamps = false;

    public function model()
    {
        return $this->belongsTo(EquipmentModel::class, 'eqm_eqmm_id', 'eqmm_id');
    }
}

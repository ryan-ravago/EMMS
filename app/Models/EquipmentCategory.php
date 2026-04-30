<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentCategory extends Model
{
    protected $table = 'equipment_categories';
    protected $primaryKey = 'eqmc_id';

    protected $fillable = ['eqmc_name', 'eqmc_parent_id'];

    public $timestamps = false;

    public function models()
    {
        return $this->hasMany(EquipmentModel::class, 'eqmm_eqmc_id', 'eqmc_id');
    }

    // Parent category
    public function parent()
    {
        return $this->belongsTo(EquipmentCategory::class, 'eqmc_parent_id');
    }

    // Child categories
    public function children()
    {
        return $this->hasMany(EquipmentCategory::class, 'eqmc_parent_id');
    }

    public function getParentPathAttribute(): string
    {
        $names = [];
        $category = $this->parent;

        while ($category) {
            $names[] = $category->eqmc_name;
            $category = $category->parent;
        }

        return implode(' > ', array_reverse($names));
    }
}

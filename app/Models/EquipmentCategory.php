<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EquipmentCategory extends Model
{
    protected $table = 'equipment_categories';

    protected $primaryKey = 'eqmc_id';

    protected $fillable = ['eqmc_name', 'eqmc_parent_id'];

    public $timestamps = false;

    // Parent category
    public function parent()
    {
        return $this->belongsTo(EquipmentCategory::class, 'eqmc_parent_id', 'eqmc_id');
    }

    // Child categories
    public function children()
    {
        return $this->hasMany(EquipmentCategory::class, 'eqmc_parent_id', 'eqmc_id');
    }

    public function equipments(): BelongsToMany
    {
        return $this->belongsToMany(
            Equipment::class,
            'equipment_unit_category',
            'eqmc_id',
            'eqm_id',
            'eqmc_id',
            'eqm_id'
        );
    }

    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(
            Equipment::class,
            'equipment_unit_category',
            'eqmc_id',
            'eqm_id',
            'eqmc_id',
            'eqm_id'
        );
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

    public function getFullPathAttribute(): string
    {
        $names = [];
        $category = $this;

        while ($category) {
            $names[] = $category->eqmc_name;
            $category = $category->parent;
        }

        return implode(' > ', array_reverse($names));
    }
}

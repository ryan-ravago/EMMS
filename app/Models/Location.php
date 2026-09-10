<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'parent_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    public function getParentPathAttribute(): string
    {
        $names = [];
        $location = $this->parent;

        while ($location) {
            $names[] = $location->name;
            $location = $location->parent;
        }

        return implode(' > ', array_reverse($names));
    }

    public function getFullPathAttribute(): string
    {
        $names = [];
        $location = $this;

        while ($location) {
            $names[] = $location->name;
            $location = $location->parent;
        }

        return implode(' > ', array_reverse($names));
    }

    public function equipmentUnits(): HasMany
    {
        return $this->hasMany(Equipment::class, 'location_id');
    }
}

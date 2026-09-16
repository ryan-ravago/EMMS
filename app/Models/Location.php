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

    protected static function booted(): void
    {
        static::saving(function (self $location) {
            if ($location->parent_id === null) {
                return;
            }

            if ((int) $location->parent_id === (int) $location->id) {
                throw new \InvalidArgumentException('A location cannot be its own parent.');
            }

            if ($location->exists && in_array((int) $location->parent_id, $location->getDescendantIds(), true)) {
                throw new \InvalidArgumentException('A location cannot have one of its own descendants as its parent.');
            }
        });
    }

    /**
     * @return array<int, int>
     */
    public function getDescendantIds(): array
    {
        $ids = [];

        foreach ($this->children()->pluck('id') as $childId) {
            $ids[] = (int) $childId;
            $ids = [...$ids, ...self::query()->find($childId)?->getDescendantIds() ?? []];
        }

        return $ids;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    public function descendants(): HasMany
    {
        return $this->children();
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

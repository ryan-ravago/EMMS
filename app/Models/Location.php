<?php

namespace App\Models;

use App\Models\Concerns\LogsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use LogsChanges;

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

        static::deleting(function (self $location) {
            if ($location->isReferenced()) {
                throw new \RuntimeException('Location cannot be deleted: '.$location->deletionBlockedReason());
            }
        });
    }

    /**
     * Records that can be moved or removed, after which the location becomes deletable.
     *
     * @return array<string, int>
     */
    public function getRemovableReferenceCounts(): array
    {
        return array_filter([
            'child locations' => $this->children()->count(),
            'equipment / accessories' => $this->equipmentUnits()->count(),
        ]);
    }

    /**
     * History that points at this location by id. Logs are never deleted, so these keep the location forever.
     * Lifecycle logs and edit logs are not listed: they store the location name/path, not a live reference.
     *
     * @return array<string, int>
     */
    public function getHistoryReferenceCounts(): array
    {
        return array_filter([
            'RFID tag logs' => AssetTagLog::query()->where('yard_id', $this->getKey())->count(),
        ]);
    }

    /**
     * The text stored in history for a location id: its full path, or a placeholder when the location is gone.
     */
    public static function pathForId(mixed $id): ?string
    {
        if ($id === null || $id === '') {
            return null;
        }

        return self::query()->find($id)?->full_path ?? "Deleted location #{$id}";
    }

    /**
     * Every record that points at this location, keyed by a readable label. Only non-zero counts are returned.
     *
     * @return array<string, int>
     */
    public function getReferenceCounts(): array
    {
        return [...$this->getRemovableReferenceCounts(), ...$this->getHistoryReferenceCounts()];
    }

    public function isReferenced(): bool
    {
        return $this->getReferenceCounts() !== [];
    }

    /**
     * For example: "2 equipment / accessories, 5 lifecycle logs".
     */
    public function referenceSummary(): string
    {
        return self::summarize($this->getReferenceCounts());
    }

    /**
     * Why this location cannot be deleted, or null when it can be.
     */
    public function deletionBlockedReason(): ?string
    {
        $history = $this->getHistoryReferenceCounts();
        $removable = $this->getRemovableReferenceCounts();

        if ($history !== []) {
            $reason = "\"{$this->name}\" appears in ".self::summarize($history).'. Logs are permanent, so this location must be kept.';

            return $removable === []
                ? $reason
                : $reason.' It is also used by '.self::summarize($removable).'.';
        }

        if ($removable !== []) {
            return "\"{$this->name}\" is still used by ".self::summarize($removable).'. Move or remove these first.';
        }

        return null;
    }

    /**
     * @param  array<string, int>  $counts
     */
    private static function summarize(array $counts): string
    {
        return collect($counts)
            ->map(fn (int $count, string $label): string => "{$count} {$label}")
            ->implode(', ');
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

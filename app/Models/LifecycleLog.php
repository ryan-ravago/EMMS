<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifecycleLog extends Model
{
    protected $fillable = [
        'asset_id',
        'action_id',
        'status_id',
        'deploy_to_loc_id',
        'deploy_to_loc_name',
        'allocate_to_equipment_id',
        'allocate_to_equipment_name',
        'remarks',
        'performed_by',
        'performed_by_name',
        'logged_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (LifecycleLog $log): void {
            $log->forceFill(static::withNameSnapshots($log->getAttributes()));
        });

        static::deleting(function (): void {
            throw new \RuntimeException('Lifecycle logs are permanent history and cannot be deleted.');
        });
    }

    /**
     * Copies the current location, allocated equipment and performer names onto the log attributes,
     * so the history keeps showing them after those records are renamed or deleted.
     * Use it directly for bulk inserts, which skip model events.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function withNameSnapshots(array $attributes): array
    {
        if (empty($attributes['deploy_to_loc_name']) && ! empty($attributes['deploy_to_loc_id'])) {
            $attributes['deploy_to_loc_name'] = Location::query()->whereKey($attributes['deploy_to_loc_id'])->value('name');
        }

        if (empty($attributes['allocate_to_equipment_name']) && ! empty($attributes['allocate_to_equipment_id'])) {
            $attributes['allocate_to_equipment_name'] = Equipment::query()->whereKey($attributes['allocate_to_equipment_id'])->value('eqm_name');
        }

        if (empty($attributes['performed_by_name']) && ! empty($attributes['performed_by'])) {
            $user = AppUser::query()->find($attributes['performed_by'], ['user_id', 'user_fname', 'user_mname', 'user_lname']);

            $attributes['performed_by_name'] = $user
                ? (trim(implode(' ', array_filter([$user->user_fname, $user->user_mname, $user->user_lname]))) ?: null)
                : null;
        }

        return $attributes;
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'asset_id', 'eqm_id');
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class, 'action_id', 'a_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id', 'status_id');
    }

    public function deployToLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'deploy_to_loc_id');
    }

    public function allocateToEquipment(): BelongsTo
    {
        return $this->belongsTo(
            Equipment::class,
            'allocate_to_equipment_id',
            'eqm_id'
        );
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(
            AppUser::class,
            'performed_by',
            'user_id'
        );
    }
}

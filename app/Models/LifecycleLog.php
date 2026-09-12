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
        'allocate_to_equipment_id',
        'remarks',
        'performed_by',
        'logged_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'logged_at' => 'datetime',
    ];

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

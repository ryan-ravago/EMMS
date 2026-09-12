<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetEditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'asset_id',
        'changes',
        'performed_by',
        'logged_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'logged_at' => 'datetime',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'asset_id', 'eqm_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'performed_by', 'user_id');
    }
}

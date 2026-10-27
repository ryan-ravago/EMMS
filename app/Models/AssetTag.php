<?php

namespace App\Models;

use App\Models\Concerns\LogsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetTag extends Model
{
    use LogsChanges;

    protected $primaryKey = 'tag_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tag_id',
        'asset_parent_id',
        'assigned_by',
        'assigned_date',
        'scanned_date',
        'scanned_by',
        'is_deleted',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'assigned_date' => 'datetime',
            'scanned_date' => 'datetime',
            'is_deleted' => 'boolean',
        ];
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'assigned_by', 'user_id');
    }

    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'scanned_by', 'user_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AssetTagLog::class, 'tag_id', 'tag_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'asset_parent_id', 'eqm_id');
    }
}

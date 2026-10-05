<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\LogsChanges;

class AssetTag extends Model
{
    use LogsChanges;

    protected $primaryKey = 'tag_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tag_id',
        'asset_parent_id',
    ];

    public $timestamps = false;

    public function logs(): HasMany
    {
        return $this->hasMany(AssetTagLog::class, 'tag_id', 'tag_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'asset_parent_id', 'eqm_id');
    }
}

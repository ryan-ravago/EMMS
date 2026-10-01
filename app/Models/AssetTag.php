<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTag extends Model
{
    protected $primaryKey = 'tag_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tag_id',
        'asset_parent_id',
    ];

    public $timestamps = false;

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'asset_parent_id', 'eqm_id');
    }
}

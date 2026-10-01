<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTagLog extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
            'received_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(AssetTag::class, 'tag_id', 'tag_id');
    }

    public function yard(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'yard_id');
    }
}

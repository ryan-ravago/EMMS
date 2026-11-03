<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One change of a scanner's status (or its first report). Repeated reports of the same status are not kept.
 */
class RfidScannerStatusLog extends Model
{
    use MassPrunable;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'reported_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function yard(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'yard_id');
    }

    /**
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()->where('received_at', '<', now()->subDays(max(1, (int) config('rfid.scanner_log_retention_days'))));
    }
}

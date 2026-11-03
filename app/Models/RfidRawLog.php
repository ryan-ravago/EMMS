<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * One read as the RFID reader server sent it (see RawLogProcessor for what happens to it).
 */
class RfidRawLog extends Model
{
    use MassPrunable;

    public const PENDING = 'pending';

    public const PROCESSED = 'processed';

    public const DUPLICATE = 'duplicate';

    public const UNKNOWN_TAG = 'unknown_tag';

    public const INVALID_TIME = 'invalid_time';

    public const FAILED = 'failed';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Rows older than the retention period, whatever happened to them (handled or not).
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()->where('received_at', '<', now()->subDays((int) config('rfid.raw_log_retention_days')));
    }
}

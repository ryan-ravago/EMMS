<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * @return array<string, string>
     */
    public static function outcomeLabels(): array
    {
        return [
            self::PENDING => 'Waiting',
            self::PROCESSED => 'Processed',
            self::DUPLICATE => 'Duplicate',
            self::UNKNOWN_TAG => 'Unknown tag',
            self::INVALID_TIME => 'Invalid time',
            self::FAILED => 'Failed',
        ];
    }

    public static function outcomeLabel(string $outcome): string
    {
        return self::outcomeLabels()[$outcome] ?? $outcome;
    }

    public static function outcomeColor(string $outcome): string
    {
        return match ($outcome) {
            self::PROCESSED => 'success',
            self::PENDING => 'info',
            self::UNKNOWN_TAG, self::INVALID_TIME => 'warning',
            self::FAILED => 'danger',
            default => 'gray',
        };
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function yard(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'yard_id');
    }

    /**
     * Rows older than the retention period, whatever happened to them (handled or not).
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()->where('received_at', '<', now()->subDays(self::retentionDays()));
    }

    /**
     * Whole days a raw row is kept (RFID_RAW_LOG_RETENTION_DAYS), never less than 1 so a bad value cannot wipe the table.
     */
    public static function retentionDays(): int
    {
        return max(1, (int) config('rfid.raw_log_retention_days'));
    }
}

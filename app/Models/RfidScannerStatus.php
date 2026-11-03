<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * The current status of one gate scanner (IN or OUT) of a yard, as relayed by the reader server.
 *
 * What a person sees is `state()`: the reported status, unless the reader server has stopped reporting.
 */
class RfidScannerStatus extends Model
{
    public const ACTIVE = 'active';

    public const INACTIVE = 'inactive';

    /** The reader server has not reported for a while, so the last status can no longer be trusted. */
    public const NO_REPORT = 'no_report';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'reported_at' => 'datetime',
            'status_changed_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'alert_since' => 'datetime',
            'last_notified_at' => 'datetime',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function stateLabels(): array
    {
        return [
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::NO_REPORT => 'No report',
        ];
    }

    public static function stateColor(string $state): string
    {
        return match ($state) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'danger',
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
     * @return HasMany<RfidScannerStatusLog, $this>
     */
    public function logs(): HasMany
    {
        // Matched on the scanner name of this record, so it is meant for one record at a time (not eager loading).
        return $this->hasMany(RfidScannerStatusLog::class, 'yard_id', 'yard_id')
            ->where('rfid_scanner_status_logs.scanner_name', $this->scanner_name);
    }

    /**
     * Minutes since the reader server last reported. Measured by the database against its own clock, because
     * last_seen_at is filled in by the database, whose timezone can differ from the application's.
     */
    public function minutesSinceLastReport(): int
    {
        $databaseNow = Carbon::parse(DB::selectOne('select CURRENT_TIMESTAMP as now')->now);

        // Both values are read as the same wall-clock text, so the difference is right whatever the database timezone is.
        return max(0, (int) Carbon::parse($this->getRawOriginal('last_seen_at'))->diffInMinutes($databaseNow, false));
    }

    public function state(): string
    {
        if ($this->minutesSinceLastReport() >= max(1, (int) config('rfid.scanner_stale_minutes'))) {
            return self::NO_REPORT;
        }

        return $this->is_active ? self::ACTIVE : self::INACTIVE;
    }

    /**
     * "Valenzuela Yard · OUT" style name for messages.
     */
    public function label(): string
    {
        return trim(($this->yard?->name ?? "Yard #{$this->yard_id}").' · '.$this->scanner_name);
    }
}

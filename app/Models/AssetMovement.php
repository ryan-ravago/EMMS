<?php

namespace App\Models;

use App\Models\Concerns\LogsChanges;
use Carbon\CarbonInterface;
use Closure;
use Database\Factories\AssetMovementFactory;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * One trip of an equipment unit: the OUT scan and the IN scan that belong together, and how the trip was verified.
 *
 * Status is derived from the columns and never stored (first match wins):
 * disregarded, acknowledged, pending acknowledgement (both times), missing time out, missing time in.
 */
class AssetMovement extends Model
{
    /** @use HasFactory<AssetMovementFactory> */
    use HasFactory;

    use LogsChanges;

    /** The counts behind the list tabs and the dashboard; cleared whenever a trip changes. */
    public const STATUS_COUNTS_CACHE_KEY = 'asset_movements.status_counts';

    public const STATUS_DISREGARDED = 'disregarded';

    public const STATUS_ACKNOWLEDGED = 'acknowledged';

    public const STATUS_PENDING_ACKNOWLEDGEMENT = 'pending_acknowledgement';

    public const STATUS_MISSING_TIME_OUT = 'missing_time_out';

    public const STATUS_MISSING_TIME_IN = 'missing_time_in';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::STATUS_COUNTS_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::STATUS_COUNTS_CACHE_KEY));
    }

    protected $fillable = [
        'asset_id',
        'tag_id',
        'out_log_id',
        'in_log_id',
        'time_out',
        'time_in',
        'yard_out_id',
        'yard_in_id',
        'manual_out_by',
        'manual_out_at',
        'manual_out_remarks',
        'manual_in_by',
        'manual_in_at',
        'manual_in_remarks',
        'acknowledged_by',
        'acknowledged_at',
        'acknowledgement_remarks',
        'disregarded_by',
        'disregarded_at',
        'disregard_remarks',
    ];

    protected function casts(): array
    {
        return [
            'time_out' => 'datetime',
            'time_in' => 'datetime',
            'manual_out_at' => 'datetime',
            'manual_in_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'disregarded_at' => 'datetime',
        ];
    }

    /**
     * @return array<string, string> status => label
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING_ACKNOWLEDGEMENT => 'Pending acknowledgement',
            self::STATUS_MISSING_TIME_OUT => 'Missing time out',
            self::STATUS_MISSING_TIME_IN => 'Missing time in',
            self::STATUS_ACKNOWLEDGED => 'Acknowledged',
            self::STATUS_DISREGARDED => 'Disregarded',
        ];
    }

    /**
     * How many trips are in each state, from one query. "attention" is every trip nobody has acknowledged or
     * disregarded; the next three split it up. Used by the list tabs and the dashboard.
     *
     * @return array{attention: int, pending: int, missing_out: int, missing_in: int, acknowledged: int, disregarded: int}
     */
    public static function statusCounts(): array
    {
        return Cache::remember(self::STATUS_COUNTS_CACHE_KEY, now()->addSeconds(60), fn (): array => self::queryStatusCounts());
    }

    /**
     * @return array{attention: int, pending: int, missing_out: int, missing_in: int, acknowledged: int, disregarded: int}
     */
    private static function queryStatusCounts(): array
    {
        $open = 'disregarded_at IS NULL AND acknowledged_at IS NULL';

        $row = (array) self::query()
            ->selectRaw("SUM({$open}) as attention")
            ->selectRaw("SUM({$open} AND time_out IS NOT NULL AND time_in IS NOT NULL) as pending")
            ->selectRaw("SUM({$open} AND time_out IS NULL) as missing_out")
            ->selectRaw("SUM({$open} AND time_in IS NULL) as missing_in")
            ->selectRaw('SUM(disregarded_at IS NULL AND acknowledged_at IS NOT NULL) as acknowledged')
            ->selectRaw('SUM(disregarded_at IS NOT NULL) as disregarded')
            ->toBase()
            ->first();

        return [
            'attention' => (int) ($row['attention'] ?? 0),
            'pending' => (int) ($row['pending'] ?? 0),
            'missing_out' => (int) ($row['missing_out'] ?? 0),
            'missing_in' => (int) ($row['missing_in'] ?? 0),
            'acknowledged' => (int) ($row['acknowledged'] ?? 0),
            'disregarded' => (int) ($row['disregarded'] ?? 0),
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'asset_id', 'eqm_id');
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(AssetTag::class, 'tag_id', 'tag_id');
    }

    public function outLog(): BelongsTo
    {
        return $this->belongsTo(AssetTagLog::class, 'out_log_id');
    }

    public function inLog(): BelongsTo
    {
        return $this->belongsTo(AssetTagLog::class, 'in_log_id');
    }

    public function yardOut(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'yard_out_id');
    }

    public function yardIn(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'yard_in_id');
    }

    public function manualOutBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'manual_out_by', 'user_id');
    }

    public function manualInBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'manual_in_by', 'user_id');
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'acknowledged_by', 'user_id');
    }

    public function disregardedBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'disregarded_by', 'user_id');
    }

    public function getStatusAttribute(): string
    {
        return match (true) {
            $this->disregarded_at !== null => self::STATUS_DISREGARDED,
            $this->acknowledged_at !== null => self::STATUS_ACKNOWLEDGED,
            $this->time_out !== null && $this->time_in !== null => self::STATUS_PENDING_ACKNOWLEDGEMENT,
            $this->time_out === null => self::STATUS_MISSING_TIME_OUT,
            default => self::STATUS_MISSING_TIME_IN,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status];
    }

    /**
     * Time away from the yard, once both times are known.
     */
    public function getDurationAttribute(): ?string
    {
        if ($this->time_out === null || $this->time_in === null) {
            return null;
        }

        return $this->time_out->diffForHumans($this->time_in, ['syntax' => CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2]);
    }

    public function isManualOut(): bool
    {
        return $this->manual_out_by !== null;
    }

    public function isManualIn(): bool
    {
        return $this->manual_in_by !== null;
    }

    public function canBeAcknowledged(): bool
    {
        return $this->status === self::STATUS_PENDING_ACKNOWLEDGEMENT;
    }

    public function canRecordManualOut(): bool
    {
        return $this->status === self::STATUS_MISSING_TIME_OUT;
    }

    public function canRecordManualIn(): bool
    {
        return $this->status === self::STATUS_MISSING_TIME_IN;
    }

    public function canBeDisregarded(): bool
    {
        return ! in_array($this->status, [self::STATUS_DISREGARDED, self::STATUS_ACKNOWLEDGED], true);
    }

    /**
     * Trips that nobody has acknowledged or disregarded yet.
     *
     * @param  Builder<AssetMovement>  $query
     * @return Builder<AssetMovement>
     */
    public function scopeNeedingAttention(Builder $query): Builder
    {
        return $query->whereNull('disregarded_at')->whereNull('acknowledged_at');
    }

    /**
     * @param  Builder<AssetMovement>  $query
     * @return Builder<AssetMovement>
     */
    public function scopePendingAcknowledgement(Builder $query): Builder
    {
        return $query->needingAttention()->whereNotNull('time_out')->whereNotNull('time_in');
    }

    /**
     * @param  Builder<AssetMovement>  $query
     * @return Builder<AssetMovement>
     */
    public function scopeMissingTimeOut(Builder $query): Builder
    {
        return $query->needingAttention()->whereNull('time_out');
    }

    /**
     * @param  Builder<AssetMovement>  $query
     * @return Builder<AssetMovement>
     */
    public function scopeMissingTimeIn(Builder $query): Builder
    {
        return $query->needingAttention()->whereNull('time_in');
    }

    /**
     * @param  Builder<AssetMovement>  $query
     * @return Builder<AssetMovement>
     */
    public function scopeAcknowledged(Builder $query): Builder
    {
        return $query->whereNull('disregarded_at')->whereNotNull('acknowledged_at');
    }

    /**
     * @param  Builder<AssetMovement>  $query
     * @return Builder<AssetMovement>
     */
    public function scopeDisregarded(Builder $query): Builder
    {
        return $query->whereNotNull('disregarded_at');
    }

    /**
     * Trips that have left and not come back (and nobody has dealt with): the ones a new IN scan closes.
     *
     * @param  Builder<AssetMovement>  $query
     * @return Builder<AssetMovement>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->missingTimeIn();
    }

    public function acknowledge(int $userId, string $remarks): void
    {
        $this->transition(function (self $movement) use ($userId, $remarks): void {
            if (! $movement->canBeAcknowledged()) {
                throw new DomainException('Only a trip with both a time out and a time in can be acknowledged.');
            }

            $movement->update([
                'acknowledged_by' => $userId,
                'acknowledged_at' => now(),
                'acknowledgement_remarks' => $remarks,
            ]);
        });
    }

    /**
     * Fills in a missing time out. Whoever does this has checked the trip, so the same step acknowledges it,
     * with the same remarks.
     */
    public function recordManualOut(CarbonInterface $timeOut, int $userId, string $remarks): void
    {
        $this->transition(function (self $movement) use ($timeOut, $userId, $remarks): void {
            if (! $movement->canRecordManualOut()) {
                throw new DomainException('A manual out is only possible when the time out is missing.');
            }

            if ($timeOut->isFuture()) {
                throw new DomainException('The time out cannot be in the future.');
            }

            if ($timeOut->greaterThan($movement->time_in)) {
                throw new DomainException('The time out cannot be later than the time in.');
            }

            $movement->update([
                'time_out' => $timeOut,
                'manual_out_by' => $userId,
                'manual_out_at' => now(),
                'manual_out_remarks' => $remarks,
            ] + $movement->manualAcknowledgement($userId, $remarks));
        });
    }

    /**
     * Fills in a missing time in. Like a manual out, it also acknowledges the trip.
     */
    public function recordManualIn(CarbonInterface $timeIn, int $userId, string $remarks): void
    {
        $this->transition(function (self $movement) use ($timeIn, $userId, $remarks): void {
            if (! $movement->canRecordManualIn()) {
                throw new DomainException('A manual in is only possible when the time in is missing.');
            }

            if ($timeIn->isFuture()) {
                throw new DomainException('The time in cannot be in the future.');
            }

            if ($timeIn->lessThan($movement->time_out)) {
                throw new DomainException('The time in cannot be earlier than the time out.');
            }

            $movement->update([
                'time_in' => $timeIn,
                'manual_in_by' => $userId,
                'manual_in_at' => now(),
                'manual_in_remarks' => $remarks,
            ] + $movement->manualAcknowledgement($userId, $remarks));
        });
    }

    public function disregard(int $userId, string $remarks): void
    {
        $this->transition(function (self $movement) use ($userId, $remarks): void {
            if (! $movement->canBeDisregarded()) {
                throw new DomainException('This trip has already been acknowledged or disregarded.');
            }

            $movement->update([
                'disregarded_by' => $userId,
                'disregarded_at' => now(),
                'disregard_remarks' => $remarks,
            ]);
        });
    }

    /**
     * Acknowledges many trips with one locked read and one UPDATE. Only the selected trips that are still waiting
     * for acknowledgement are changed, so a trip someone else has just dealt with is left alone.
     *
     * @param  array<int, int|string>  $ids
     * @return int how many trips were acknowledged
     */
    public static function acknowledgeMany(array $ids, int $userId, string $remarks): int
    {
        return self::changeMany(
            $ids,
            fn (Builder $query): Builder => $query->pendingAcknowledgement(),
            [
                'acknowledged_by' => $userId,
                'acknowledged_at' => now(),
                'acknowledgement_remarks' => $remarks,
            ],
        );
    }

    /**
     * Disregards many trips with one locked read and one UPDATE. Trips that are already acknowledged or
     * disregarded are left alone.
     *
     * @param  array<int, int|string>  $ids
     * @return int how many trips were disregarded
     */
    public static function disregardMany(array $ids, int $userId, string $remarks): int
    {
        return self::changeMany(
            $ids,
            fn (Builder $query): Builder => $query->needingAttention(),
            [
                'disregarded_by' => $userId,
                'disregarded_at' => now(),
                'disregard_remarks' => $remarks,
            ],
        );
    }

    /**
     * Applies the same change to every selected trip that is still eligible. The UPDATE skips model events, so each
     * changed trip is written to the activity log here, in the same shape a model update would have produced,
     * and the cached counts are cleared once.
     *
     * @param  array<int, int|string>  $ids
     * @param  Closure(Builder<self>): Builder<self>  $eligible
     * @param  array<string, mixed>  $changes
     */
    private static function changeMany(array $ids, Closure $eligible, array $changes): int
    {
        if ($ids === []) {
            return 0;
        }

        return DB::transaction(function () use ($ids, $eligible, $changes): int {
            $trips = $eligible(self::query()->whereIn('id', $ids))->lockForUpdate()->get();

            if ($trips->isEmpty()) {
                return 0;
            }

            self::query()->whereIn('id', $trips->modelKeys())->update($changes + ['updated_at' => now()]);

            $new = collect($changes)->map(fn (mixed $value): mixed => $value instanceof CarbonInterface ? $value->format('Y-m-d H:i:s') : $value)->all();
            $old = array_map(fn (): mixed => null, $new);

            foreach ($trips as $trip) {
                activity()
                    ->performedOn($trip)
                    ->event('updated')
                    ->withProperties(['attributes' => $new, 'old' => $old])
                    ->log('updated');
            }

            Cache::forget(self::STATUS_COUNTS_CACHE_KEY);

            return $trips->count();
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function manualAcknowledgement(int $userId, string $remarks): array
    {
        return [
            'acknowledged_by' => $userId,
            'acknowledged_at' => now(),
            'acknowledgement_remarks' => $remarks,
        ];
    }

    /**
     * Runs a state change on the freshly locked row, so two people acting on the same trip cannot both succeed.
     * This instance is refreshed with the result.
     *
     * @param  callable(self): void  $change
     */
    private function transition(callable $change): void
    {
        DB::transaction(function () use ($change): void {
            $locked = self::query()->lockForUpdate()->findOrFail($this->getKey());

            $change($locked);

            $this->setRawAttributes($locked->getAttributes(), sync: true);
        });
    }
}

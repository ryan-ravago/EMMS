<?php

namespace App\Services\Rfid;

use App\Models\AssetTagLog;
use App\Models\RfidRawLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Works out whether the RFID reader server is getting its scans to EMS, using these signals:
 *
 *  - the last request it made: the newest of its token's last_used_at (Sanctum) and the newest raw read stored
 *    (rfid_raw_logs, which keeps reads of tags EMS does not know too), so it proves the reader server is alive;
 *  - the last scan that was stored (asset_tag_logs.received_at);
 *  - whether raw reads are waiting to be processed for too long (the queue worker or scheduler is down).
 */
class RfidHealthCheck
{
    public function inspect(): RfidHealthReport
    {
        $tokens = $this->validTokens();
        $minutesSinceRawRead = $this->minutesSince(RfidRawLog::query());
        $lastRequestAt = collect([
            $tokens->max('last_used_at'),
            $minutesSinceRawRead === null ? null : now()->subMinutes($minutesSinceRawRead),
        ])->filter()->max();
        $minutesSinceScan = $this->minutesSince(AssetTagLog::query());
        $tokenExpiresAt = $this->tokenExpiry($tokens);

        if ($tokens->isEmpty()) {
            return new RfidHealthReport(RfidHealthReport::DOWN, RfidHealthReport::CAUSE_NO_TOKEN, null, $minutesSinceScan, null);
        }

        // Reads are being saved but nothing moves them on: the queue worker or scheduler is not running.
        $oldestWaiting = $this->minutesSince(RfidRawLog::query()->where('outcome', RfidRawLog::PENDING), 'min');

        if ($oldestWaiting !== null && $oldestWaiting >= (int) config('rfid.stuck_after_minutes')) {
            return new RfidHealthReport(RfidHealthReport::DOWN, RfidHealthReport::CAUSE_STUCK, $lastRequestAt, $minutesSinceScan, $tokenExpiresAt);
        }

        $silence = (int) config('rfid.alerts.silence_minutes');
        $noScans = (int) config('rfid.alerts.no_scans_minutes');

        $neverContacted = $lastRequestAt === null || $lastRequestAt->lte(now()->subMinutes($silence));
        $scansStale = $minutesSinceScan === null || $minutesSinceScan >= $noScans;

        [$status, $cause] = match (true) {
            $neverContacted && $scansStale => [RfidHealthReport::DOWN, RfidHealthReport::CAUSE_NO_CONTACT],
            ! $neverContacted && $scansStale => [RfidHealthReport::QUIET, RfidHealthReport::CAUSE_NO_SCANS],
            default => [RfidHealthReport::OK, null],
        };

        return new RfidHealthReport($status, $cause, $lastRequestAt, $minutesSinceScan, $tokenExpiresAt);
    }

    /**
     * @return Collection<int, PersonalAccessToken>
     */
    private function validTokens(): Collection
    {
        return PersonalAccessToken::query()
            ->where('name', config('rfid.token_name'))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->get();
    }

    /**
     * The date the reader loses access, or null when a token that never expires exists (or there is none).
     *
     * @param  Collection<int, PersonalAccessToken>  $tokens
     */
    private function tokenExpiry(Collection $tokens): ?Carbon
    {
        if ($tokens->isEmpty() || $tokens->contains(fn (PersonalAccessToken $token): bool => $token->expires_at === null)) {
            return null;
        }

        return $tokens->max('expires_at');
    }

    /**
     * Minutes since the newest (or with 'min', the oldest) received_at of the rows the query selects, or null when
     * there are none. Measured by the database against its own clock: received_at is filled in by the database,
     * whose timezone can differ from the application's.
     *
     * @param  Builder<*>  $query
     * @param  'max'|'min'  $aggregate
     */
    private function minutesSince(Builder $query, string $aggregate = 'max'): ?int
    {
        $received = $aggregate === 'min' ? $query->min('received_at') : $query->max('received_at');

        if ($received === null) {
            return null;
        }

        $databaseNow = DB::selectOne('select CURRENT_TIMESTAMP as now')->now;

        return max(0, (int) Carbon::parse($received)->diffInMinutes(Carbon::parse($databaseNow), false));
    }
}

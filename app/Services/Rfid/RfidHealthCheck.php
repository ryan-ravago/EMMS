<?php

namespace App\Services\Rfid;

use App\Models\AssetTagLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Works out whether the RFID reader server is getting its scans to EMMS, using two signals:
 *
 *  - the last request it made with its token (Sanctum's last_used_at). Requests for tags EMMS does not
 *    know are rejected and store nothing, but they still prove the reader server is alive;
 *  - the last scan that was stored (asset_tag_logs.received_at).
 */
class RfidHealthCheck
{
    public function inspect(): RfidHealthReport
    {
        $tokens = $this->validTokens();
        $lastRequestAt = $tokens->max('last_used_at');
        $minutesSinceScan = $this->minutesSinceLastScan();
        $tokenExpiresAt = $this->tokenExpiry($tokens);

        if ($tokens->isEmpty()) {
            return new RfidHealthReport(RfidHealthReport::DOWN, RfidHealthReport::CAUSE_NO_TOKEN, null, $minutesSinceScan, null);
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
     * Measured by the database against its own clock: received_at is filled in by the database, whose
     * timezone can differ from the application's.
     */
    private function minutesSinceLastScan(): ?int
    {
        $lastScan = AssetTagLog::query()->max('received_at');

        if ($lastScan === null) {
            return null;
        }

        $databaseNow = DB::selectOne('select CURRENT_TIMESTAMP as now')->now;

        return max(0, (int) Carbon::parse($lastScan)->diffInMinutes(Carbon::parse($databaseNow), false));
    }
}

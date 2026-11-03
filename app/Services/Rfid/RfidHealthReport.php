<?php

namespace App\Services\Rfid;

use Carbon\CarbonInterface;

/**
 * What the RFID reader looks like right now, in words a person can act on.
 */
final class RfidHealthReport
{
    public const OK = 'ok';

    /** The reader server is contacting EMMS, but no known tag has been stored for a while. */
    public const QUIET = 'quiet';

    /** The reader server is not reaching EMMS (or cannot: no valid token). */
    public const DOWN = 'down';

    public const CAUSE_NO_TOKEN = 'no_valid_token';

    public const CAUSE_NO_CONTACT = 'no_contact';

    public const CAUSE_NO_SCANS = 'no_scans';

    public function __construct(
        public readonly string $status,
        public readonly ?string $cause,
        public readonly ?CarbonInterface $lastRequestAt,
        public readonly ?int $minutesSinceScan,
        public readonly ?CarbonInterface $tokenExpiresAt,
    ) {}

    public function isHealthy(): bool
    {
        return $this->status === self::OK;
    }

    /**
     * Whether the last token that can still be used is about to expire (a token without an expiry never does).
     */
    public function tokenExpiresWithin(int $days): bool
    {
        return $this->tokenExpiresAt !== null && $this->tokenExpiresAt->lte(now()->addDays($days));
    }

    public function headline(): string
    {
        return match ($this->cause) {
            self::CAUSE_NO_TOKEN => 'The RFID reader has no valid token',
            self::CAUSE_NO_CONTACT => 'The RFID reader server is not reaching EMMS',
            self::CAUSE_NO_SCANS => 'The RFID reader is connected but no tag scans are being stored',
            default => 'The RFID reader is working',
        };
    }

    public function explanation(): string
    {
        return match ($this->cause) {
            self::CAUSE_NO_TOKEN => 'Every token for the reader server has expired or been revoked, so EMMS rejects each scan with "401 Unauthenticated".',
            self::CAUSE_NO_CONTACT => 'EMMS has not received any request from the reader server for a while. The server may be off, offline, or not sending.',
            self::CAUSE_NO_SCANS => 'The reader server is sending data, but none of it was a tag that EMMS knows. The readers may be off, or the tags are not registered.',
            default => '',
        };
    }

    /**
     * @return list<string>
     */
    public function steps(): array
    {
        return match ($this->cause) {
            self::CAUSE_NO_TOKEN => [
                'On the EMMS server run: php artisan rfid:issue-token --revoke',
                'Put the new token on the reader server (it is shown only once).',
            ],
            self::CAUSE_NO_CONTACT => [
                'Check that the reader server is on and has internet access.',
                'Check that it still has the current token and the right EMMS address.',
                'If the token was changed, issue a new one: php artisan rfid:issue-token --revoke',
            ],
            self::CAUSE_NO_SCANS => [
                'Check that the RFID readers are powered and connected to the reader server.',
                'Check that the tags being read are registered under RFID Tags.',
            ],
            default => [],
        };
    }
}

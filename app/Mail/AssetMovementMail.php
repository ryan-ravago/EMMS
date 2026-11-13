<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * One email per time out / time in of an equipment or service vehicle (about the asset, not its RFID tags).
 */
class AssetMovementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public const OUT = 'out';

    public const IN = 'in';

    /**
     * Plain values only (no models), so a queued mail still sends if the trip changes or is removed afterwards.
     *
     * @param  'out'|'in'  $kind
     * @param  string  $assetLabel  "CODE - Name"
     * @param  array<string, string>  $details  label => value
     */
    public function __construct(
        public string $kind,
        public string $assetLabel,
        public string $assetType,
        public array $details,
        public ?string $note = null,
        public ?string $tripUrl = null,
        public ?string $profileUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        $event = $this->kind === self::OUT ? 'Time out' : 'Time in';

        return new Envelope(subject: "[EMS] {$event}: {$this->assetLabel}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.asset-movement');
    }
}

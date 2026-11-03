<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RfidHealthAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public const PROBLEM = 'problem';

    public const REMINDER = 'reminder';

    public const RECOVERED = 'recovered';

    public const TOKEN_EXPIRING = 'token_expiring';

    /**
     * @param  'problem'|'reminder'|'recovered'|'token_expiring'  $kind
     * @param  array<string, string>  $details  label => value
     * @param  list<string>  $steps
     */
    public function __construct(
        public string $kind,
        public string $headline,
        public string $explanation,
        public array $details = [],
        public array $steps = [],
    ) {}

    public function envelope(): Envelope
    {
        $prefix = match ($this->kind) {
            self::RECOVERED => '[RFID RECOVERED]',
            self::REMINDER => '[RFID STILL DOWN]',
            self::TOKEN_EXPIRING => '[RFID TOKEN EXPIRING]',
            default => '[RFID ALERT]',
        };

        return new Envelope(subject: "{$prefix} {$this->headline}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.rfid.alert');
    }
}

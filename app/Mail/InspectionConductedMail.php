<?php

namespace App\Mail;

use App\Models\Inspection;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InspectionConductedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Inspection $inspection,
        public string $recipientType, // 'technician' | 'manager'
        public bool $hasFailedItems,
        public Collection $failedItems,
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match (true) {
            $this->recipientType === 'technician' && $this->hasFailedItems => "Inspection Conducted – Failed Items Detected | {$this->inspection->equipment->eqm_name}",
            $this->recipientType === 'technician' && ! $this->hasFailedItems => "Inspection Conducted – All Items Passed | {$this->inspection->equipment->eqm_name}",
            $this->recipientType === 'manager' && $this->hasFailedItems => "Inspection Requires Review | {$this->inspection->equipment->eqm_name}",
            default => "Inspection Update – No Issues Found | {$this->inspection->equipment->eqm_name}",
        };

        return new Envelope(subject: $subject);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(view: 'emails.inspection-conducted');
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

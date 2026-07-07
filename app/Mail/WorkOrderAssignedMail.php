<?php

namespace App\Mail;

use App\Models\AppUser;
use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkOrderAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public WorkOrder $workOrder,
        public ?AppUser $worker = null,
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $replyToAddress = $this->buildReplyToAddress();

        return new Envelope(
            subject: "[WO:{$this->workOrder->wo_no}] Action Required",
            replyTo: $replyToAddress ? [new Address($replyToAddress, config('app.name'))] : [],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.work-orders.assigned',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $disk = config('filament.default_filesystem_disk', config('filesystems.default'));

        return collect($this->workOrder->wo_attachments ?? [])
            ->filter(fn ($path): bool => is_string($path) && trim($path) !== '')
            ->map(fn (string $path): Attachment => Attachment::fromStorageDisk($disk, $path))
            ->values()
            ->all();
    }

    private function buildReplyToAddress(): ?string
    {
        $replyToAddress = config('work_orders.reply_to_address');

        if (! $replyToAddress || ! str_contains($replyToAddress, '@')) {
            return $replyToAddress;
        }

        [$localPart, $domainPart] = explode('@', $replyToAddress, 2);
        $threadToken = trim($this->workOrder->wo_no);

        if ($threadToken === '') {
            return $replyToAddress;
        }

        return sprintf('%s+%s@%s', $localPart, $threadToken, $domainPart);
    }
}

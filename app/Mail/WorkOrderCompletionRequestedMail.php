<?php

namespace App\Mail;

use App\Models\AppUser;
use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkOrderCompletionRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public WorkOrder $workOrder,
        public AppUser $requestor,
        public string $recipientType, // 'requestor' | 'manager' | 'technician'
        public ?string $note = null,
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->recipientType) {
            'requestor'  => "Work Order Completion Requested – {$this->workOrder->wo_no}",
            'manager'    => "Action Required: Completion Approval – {$this->workOrder->wo_no}",
            'technician' => "Work Order Update – {$this->workOrder->wo_no}",
        };

        return new Envelope(subject: $subject);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.work-orders.work-order-completion-requested',
        );
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

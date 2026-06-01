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

class WorkOrderCancellationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public WorkOrder $workOrder,
        public AppUser $canceller,
        public string $reason,
        public string $recipientType, // 'manager' | 'technician'
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->recipientType) {
            'manager'    => "Work Order Cancelled – {$this->workOrder->wo_no}",
            'technician' => "Work Order Cancelled – {$this->workOrder->wo_no}",
            default      => "Work Order Update – {$this->workOrder->wo_no}",
        };

        return new Envelope(subject: $subject);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.work-orders.work-order-cancellation'
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

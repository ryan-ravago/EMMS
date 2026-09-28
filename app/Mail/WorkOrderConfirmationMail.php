<?php

namespace App\Mail;

use App\Mail\Concerns\HasWorkOrderReplyTo;
use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkOrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels, HasWorkOrderReplyTo;

    /**
     * Create a new message instance.
     */
    public function __construct(public WorkOrder $workOrder)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $replyToAddress = $this->buildReplyToAddress();

        return new Envelope(
            subject: "Work Order Confirmed: {$this->workOrder->wo_no}",
            replyTo: $replyToAddress ? [new Address($replyToAddress, config('app.name'))] : [],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.work-orders.confirmation',
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

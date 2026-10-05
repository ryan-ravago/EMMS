<?php

namespace App\Mail;

use App\Models\AppUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmmsAccessGrantedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AppUser $user,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You now have access to ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.access-granted',
            with: ['loginUrl' => url('/login')],
        );
    }
}

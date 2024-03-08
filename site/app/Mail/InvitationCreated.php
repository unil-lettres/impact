<?php

namespace App\Mail;

use App\Enums\InvitationType;
use App\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class InvitationCreated extends Mailable
{
    use Queueable, SerializesModels;

    public Invitation $invitation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the message headers.
     */
    public function headers(): Headers
    {
        return new Headers(
            text: [
                'X-Tags' => 'Impact',
            ],
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject(trans('invitations.mail.subject'));

        return match ($this->invitation->type) {
            InvitationType::Local => $this->view('mails.invitations.local'),
            // InvitationType::Aai
            default => $this->view('mails.invitations.aai'),
        };
    }
}

<?php

namespace App\Mail;

use App\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The request instance.
     *
     * @var Invitation
     */
    public $invitation;

    /**
     * Create a new message instance.
     *
     * @param  Invitation  $invitation
     * @return void
     */
    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(trans('invitations.mail.create.subject'))
            ->view('mails.invitations.created');
    }
}

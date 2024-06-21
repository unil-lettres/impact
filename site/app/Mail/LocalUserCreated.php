<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class LocalUserCreated extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
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
        return $this->subject(trans('users.email.local.account.created.subject'))
            ->view('mails.users.local');
    }
}

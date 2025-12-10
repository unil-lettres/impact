<?php

namespace App\Mail;

use App\Course;
use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class CourseConfirmDelete extends Mailable
{
    use Queueable, SerializesModels;

    public Course $course;

    public string $fromEmail;

    public string $contactList;

    /**
     * Create a new message instance.
     */
    public function __construct(Course $course)
    {
        $this->course = $course;
        $this->fromEmail = Auth::user()?->email ?: config('mail.from.address');
        $this->contactList = Helpers::getContactList();
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
        return $this->subject(trans('courses.mail.confirm_delete.subject'))
            ->from($this->fromEmail)
            ->view('mails.courses.confirm_delete');
    }
}

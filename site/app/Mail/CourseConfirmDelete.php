<?php

namespace App\Mail;

use App\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class CourseConfirmDelete extends Mailable
{
    use Queueable, SerializesModels;

    public Course $course;

    /**
     * Create a new message instance.
     */
    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(trans('courses.mail.confirm_delete.subject'))
            ->from(Auth::user()->email)
            ->view('mails.courses.confirm_delete');
    }
}

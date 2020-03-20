<?php

namespace App\Mail;

use App\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class CourseConfirmDelete extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The request instance.
     *
     * @var Course
     */
    public $course;

    /**
     * Create a new message instance.
     *
     * @param Course $course
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

<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class TeachersMailing extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public Collection $courses;

    public $subject;

    public string $content;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $subject, string $content, Collection $courses)
    {
        $this->user = $user;
        $this->courses = $courses;
        $this->subject = $subject;
        $this->content = $this->processContent($content);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
            ->from($this->user->email)
            ->view('mails.mailing.mailing');
    }

    /**
     * Clean content & replace placeholder.
     */
    private function processContent(string $content): string
    {
        // Replace {{espaces}} placeholder with courses if found
        $content = str_replace(
            '{{espaces}}',
            $this->coursesAsHtmlList(),
            $content
        );

        // Clean content if needed
        return str_replace(
            "\r\n",
            '<br/>',
            $content
        );
    }

    /**
     * Return an HTML list of the courses.
     */
    private function coursesAsHtmlList(): string
    {
        if ($this->courses) {
            $html = '';
            foreach ($this->courses as $course) {
                $html .= '- <a href="'.route('courses.show', $course->id).'">'.$course->name.'</a>';

                if ($course !== $this->courses->last()) {
                    $html .= '<br/>';
                }
            }
        }

        return $html ?? '';
    }
}

<?php

namespace App\Mail;

use App\Card;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StateSelected extends Mailable
{
    use Queueable, SerializesModels;

    public $card;

    public $subject;

    public $content;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Card $card, string $subject, string $content)
    {
        $this->card = $card;
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
            ->view('mails.states.selected');
    }

    /**
     * Clean content & replace placeholders
     *
     * @param  string  $content
     * @return string
     */
    private function processContent($content)
    {
        // Replace {{title}} & {{url}} placeholders if found
        $search = [
            '{{title}}',
            '{{url}}',
        ];
        $replace = [
            $this->card->title,
            url("/cards/{$this->card->id}"),
        ];
        $content = str_replace($search, $replace, $content);

        // Clean content if needed
        return str_replace(
            "\r\n",
            '<br/>',
            $content
        );
    }
}

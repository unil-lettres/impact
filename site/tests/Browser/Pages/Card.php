<?php

namespace Tests\Browser\Pages;

use App\Card as AppCard;

class Card extends Page
{
    private AppCard $card;

    public function __construct(string $cardName){
        $this->card = AppCard::where('title', $cardName)->first();
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return "/cards/{$this->card->id}";
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@element' => '#selector',
        ];
    }
}

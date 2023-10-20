<?php

namespace Tests\Browser\Pages;

use App\Card as AppCard;
use Laravel\Dusk\Browser;

class Card extends Page
{
    private AppCard $card;

    public function __construct(string $cardName){
        $this->card = AppCard::where('title', $cardName)->first();
    }

    /**
     * Get the URL for the page.
     */
    public function url() : string
    {
        return "/cards/{$this->card->id}";
    }

    /**
     * Wait to the finder to be fully loaded.
     */
    public function waitUntilLoaded(Browser $browser): void
    {
        $browser->waitForText($this->card->title);
    }

    /**
     * Return the id of the card.
     */
    public function id(): int
    {
        return $this->card->id;
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@element' => '#selector',
        ];
    }
}

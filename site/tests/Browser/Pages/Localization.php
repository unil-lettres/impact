<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Localization extends Page
{
    protected $locale;

    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return 'lang/'.$this->locale;
    }

    /**
     * Assert that the browser is on the page.
     *
     * @return void
     */
    public function assert(Browser $browser)
    {
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

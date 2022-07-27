<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Profile extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser  $browser
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

    /**
     * Go to the user's profile page.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function profile(Browser $browser)
    {
        $browser->click('.navbar ul li.auth')
            ->clickLink('Gérer mon profil');
    }
}

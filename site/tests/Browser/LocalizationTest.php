<?php

namespace Tests\Browser;

use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Localization;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LocalizationTest extends DuskTestCase
{
    use ProvidesBrowser;

    public function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * Test basic localization.
     *
     * @return void
     */
    public function testBasicLocalization()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');

            $browser->assertSee('Connexion avec SWITCHaai');
            $browser->assertSee('Connexion locale');

            $browser->visit(new Localization('en'));

            $browser->assertSee('Connection with SWITCHaai');
            $browser->assertSee('Local connection');
        });
    }
}

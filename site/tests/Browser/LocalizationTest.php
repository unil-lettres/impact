<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Localization;
use Tests\DuskTestCase;
use Throwable;

class LocalizationTest extends DuskTestCase
{
    use ProvidesBrowser;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh --seed');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * Test basic localization.
     *
     * @throws Throwable
     */
    public function test_basic_localization(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            $browser->waitForText('Connexion locale')
                ->assertSee('Connexion locale')
                ->assertSee('Pour les utilisateurs non-AAI qui ont reçu un compte local pour Impact.');

            $browser->visit(new Localization('en'));
            $browser->waitForText('Local connection')
                ->assertSee('Local connection')
                ->assertSee('For non-IAA users who received a local account for Impact.');
        });
    }
}

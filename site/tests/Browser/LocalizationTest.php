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

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh --seed');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * Test basic localization.
     *
     * @throws Throwable
     */
    public function testBasicLocalization(): void
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

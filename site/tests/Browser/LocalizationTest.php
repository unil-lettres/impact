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

    protected static bool $migrated = false;

    public function setUp(): void
    {
        parent::setUp();

        if (! static::$migrated) {
            Artisan::call('migrate:fresh --seed');
            static::$migrated = true;
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * Test basic localization.
     *
     * @return void
     *
     * @throws Throwable
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

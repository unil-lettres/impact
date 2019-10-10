<?php

namespace Tests\Browser;

use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LoginTest extends DuskTestCase
{
    use ProvidesBrowser;

    public function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testBasicLogin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('first-user@example.com', 'password');

            $browser->assertSee('Impact content');
        });
    }
}

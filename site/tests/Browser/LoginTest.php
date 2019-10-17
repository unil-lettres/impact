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
     * Test basic login.
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

    /**
     * Test basic admin login.
     *
     * @return void
     */
    public function testBasicAdminLogin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');
            $browser->assertSee('Administration');

            $browser->clickLink('Administration');
            $browser->assertSee('Administration content');
        });
    }

    /**
     * Test invalid user.
     *
     * @return void
     */
    public function testInvalidUser()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('invalid-user@example.com', 'password');
            $browser->assertSee('Ce compte est désactivé');
        });
    }
}

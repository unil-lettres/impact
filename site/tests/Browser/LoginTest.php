<?php

namespace Tests\Browser;

use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Throwable;

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
     * @throws Throwable
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
     * @throws Throwable
     */
    public function testBasicAdminLogin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');
            $browser->assertSee('Admin');

            $browser->clickLink('Admin');
            $browser->assertSee('Utilisateurs');
        });
    }

    /**
     * Test invalid user.
     *
     * @return void
     * @throws Throwable
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

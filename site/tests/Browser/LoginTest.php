<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Throwable;

class LoginTest extends DuskTestCase
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
     * Test basic login.
     *
     * @throws Throwable
     */
    public function testBasicLogin(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('first-user@example.com', 'password');

            $browser->assertSee('Liste des espaces')
                ->assertPathIs('/');
        });
    }

    /**
     * Test basic admin login.
     *
     * @throws Throwable
     */
    public function testBasicAdminLogin(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->assertSee('Admin');

            $browser->clickLink('Admin')
                ->assertSee('Gestion des utilisateurs')
                ->assertPathIs('/admin/users');
        });
    }

    /**
     * Test invalid user.
     *
     * @throws Throwable
     */
    public function testInvalidUser(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('invalid-user@example.com', 'password');

            $browser->assertSee('Ces identifiants ne correspondent pas à nos enregistrements')
                ->assertPathIs('/login');
        });
    }
}

<?php

namespace Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Throwable;

class NavigationTest extends DuskTestCase
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
     * Test the responsiveness of the navigation bar.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testResponsiveNavbar()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->with('.navbar', function (Browser $browser) {
                $browser->assertSee('Admin')
                    ->assertSee('Français')
                    ->assertMissing('button.navbar-toggler');
            });

            $browser->resize(820, 1080);

            $browser->with('.navbar', function (Browser $browser) {
                $browser->assertDontSee('Admin')
                    ->assertDontSee('Français')
                    ->assertVisible('button.navbar-toggler');

                $browser->click('button.navbar-toggler')
                    ->waitForText('Admin')
                    ->assertSee('Admin')
                    ->waitForText('Français')
                    ->assertSee('Français');
            });
        });
    }
}

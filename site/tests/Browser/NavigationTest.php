<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Throwable;

class NavigationTest extends DuskTestCase
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
     * Test the responsiveness of the navigation bar.
     *
     * @throws Throwable
     */
    public function test_responsive_navbar(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->with('.navbar', function (Browser $browser) {
                $browser->waitForText('Admin')
                    ->assertSee('Admin')
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

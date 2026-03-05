<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Card as PagesCard;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Throwable;

class AttachmentTest extends DuskTestCase
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
     * Test can list the attachments of a card.
     *
     * @throws Throwable
     */
    public function test_list_attachments(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new PagesCard('Test card with file'));

            $browser->with('.box5', function (Browser $browser) {
                $browser->assertSee('My attachment')
                    ->assertDontSee('Pas d\'annexes');
            });
        });
    }

}

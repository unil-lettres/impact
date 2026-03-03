<?php

namespace Tests\Browser;

use App\Card as AppCard;
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
     * Test interface to upload attachments.
     *
     * @throws Throwable
     */
    public function test_open_upload_attachments_interface(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new PagesCard('Test card with file'));

            $browser->waitFor('@attachments-uploader')
                ->waitFor('@attachments-uploader .btn-primary')
                ->click('@attachments-uploader .btn-primary')
                ->waitFor('.uppy-Dashboard--modal[aria-hidden="false"]')
                ->assertPresent('.uppy-Dashboard--modal[aria-hidden="false"]');
        });
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

    /**
     * Test can delete an attachment.
     *
     * @throws Throwable
     */
    public function test_can_delete_attachment(): void
    {
        $this->browse(function (Browser $browser) {
            $card = AppCard::where('title', 'Test card with file')->first();
            $attachment = $card?->attachments()->first();

            $this->assertNotNull($attachment);

            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new PagesCard('Test card with file'));

            $browser->waitFor("@attachment-row-{$attachment->id}")
                ->scrollTo("[dusk='attachment-delete-{$attachment->id}']")
                ->click("@attachment-delete-{$attachment->id}")
                ->waitForDialog(10)
                ->acceptDialog()
                ->waitUntilMissing("[dusk='attachment-row-{$attachment->id}']")
                ->assertDontSee('My attachment');

            $this->assertTrue(
                AppCard::where('id', $card->id)
                    ->first()
                    ->attachments()
                    ->where('id', $attachment->id)
                    ->doesntExist()
            );
        });
    }
}

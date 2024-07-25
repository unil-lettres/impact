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
     * Test interface to upload attachments.
     *
     * @throws Throwable
     */
    public function testOpenUploadAttachmentsInterface(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new PagesCard('Test card with file'));

            $browser->click('#rct-attachments .btn-primary')
                ->waitForText('Déposer les fichiers ici')
                ->assertSee('Déposer les fichiers ici');
        });
    }

    /**
     * Test can list the attachments of a card.
     *
     * @throws Throwable
     */
    public function testListAttachments(): void
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
    public function testCanDeleteAttachment(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new PagesCard('Test card with file'));

            $browser->waitForText('My attachment')
                ->assertSee('My attachment');

            $browser->with('.box5 .attachments-list div:first-child', function ($attachment) {
                $attachment->click('button.btn-danger')
                    ->waitForDialog($seconds = null)
                    ->assertDialogOpened('Êtes-vous sûr de vouloir supprimer cet élément ?')
                    ->acceptDialog();
            });

            $browser->waitForText('Pas d\'annexes')
                ->assertSee('Pas d\'annexes')
                ->assertDontSee('My attachment');
        });
    }
}

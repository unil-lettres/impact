<?php

namespace Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Throwable;

class AttachmentTest extends DuskTestCase
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
     * Test interface to upload attachments.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testOpenUploadAttachmentsInterface()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->assertSee('Second space')
                ->clickLink('Second space');

            $browser->assertSee('Test card with file')
                ->clickLink('Test card with file');

            $browser->click('#rct-attachments .btn-primary')
                ->waitForText('Déposer les fichiers ici')
                ->assertSee('Déposer les fichiers ici');
        });
    }

    /**
     * Test can list the attachments of a card.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testListAttachments()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->assertSee('Second space')
                ->clickLink('Second space');

            $browser->assertSee('Test card with file')
                ->clickLink('Test card with file');

            $browser->with('.box5', function (Browser $browser) {
                $browser->assertSee('My attachment')
                    ->assertDontSee('Pas d\'annexes');
            });
        });
    }

    /**
     * Test can delete an attachment.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testCanDeleteAttachment()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->assertSee('Second space')
                ->clickLink('Second space');

            $browser->assertSee('Test card with file')
                ->clickLink('Test card with file');

            $browser->with('.box5 .attachments-list', function ($unused) {
                $unused->click('button.btn-danger')
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

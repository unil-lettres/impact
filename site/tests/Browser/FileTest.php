<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Course;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Throwable;

class FileTest extends DuskTestCase
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
     * Test list files as an admin.
     *
     * @throws Throwable
     */
    public function test_list_files_as_admin(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/files');

            $browser->assertSee('Test video file')
                ->assertSee('Test audio file')
                ->assertSee('Failed file')
                ->assertSee('Used file')
                ->assertDontSee('Deactivated file');
        });
    }

    /**
     * Test list files as a manager.
     *
     * @throws Throwable
     */
    public function test_list_files_as_manager(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('manager-user@example.com', 'password');

            $browser->on(new Course('Second space'))
                ->filesIndex();

            $browser->assertSee('Test video file')
                ->assertDontSee('Test audio file')
                ->assertSee('Failed file')
                ->assertSee('Used file')
                ->assertDontSee('Deactivated file');
        });
    }

    /**
     * Test show linked card as a manager.
     *
     * @throws Throwable
     */
    public function test_show_linked_card_as_manager(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('manager-user@example.com', 'password');

            $browser->on(new Course('Second space'))
                ->filesIndex();

            $browser->with('#files table tbody tr.ready.used', function ($used) {
                $used->click('span.base-popover');
            });
            $browser->waitForText('Test card with file')
                ->assertSee('Test card with file')
                ->clickLink('Test card with file')
                ->assertSee('Test card with file');
        });
    }

    /**
     * Test edit file.
     *
     * @throws Throwable
     */
    public function test_edit_file(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/files');

            $browser->click('#files table tbody tr.ready .actions span:nth-child(1) a')
                ->type('name', 'Test file updated')
                ->click('#rct-single-course-select')
                ->waitForText('First space')
                ->assertSee('First space')
                ->assertDontSee('Deactivated space')
                ->click('#react-select-2-option-0')
                ->press('Mettre à jour le fichier')
                ->waitForText('Fichier mis à jour.')
                ->assertSee('Fichier mis à jour.');
        });
    }

    /**
     * Test can play a file with the ready status.
     *
     * @throws Throwable
     */
    public function test_can_play_ready_file(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/files');

            $browser->click('#files table tbody tr.ready .actions span:nth-child(1) a')
                ->assertInputValue('status', 'ready')
                ->assertSourceHas('Url du fichier');
        });
    }

    /**
     * Test cannot play a file with the failed status.
     *
     * @throws Throwable
     */
    public function test_cannot_play_failed_file(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/files');

            $browser->click('#files table tbody tr.failed .actions span:nth-child(1) a')
                ->assertInputValue('status', 'failed')
                ->assertSourceMissing('Url du fichier');
        });
    }

    /**
     * Test cannot play a file with the transcoding status.
     *
     * @throws Throwable
     */
    public function test_cannot_play_transcoding_file(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/files');

            $browser->click('#files table tbody tr.transcoding .actions span:nth-child(1) a')
                ->assertInputValue('status', 'transcoding')
                ->assertSourceMissing('Url du fichier');
        });
    }

    /**
     * Test cannot edit the course of a used file.
     *
     * @throws Throwable
     */
    public function test_cannot_edit_course_of_used_file(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/files');

            $browser->click('#files table tbody tr.ready.used .actions span:nth-child(1) a')
                ->assertSee('Test card with file')
                ->click('#rct-single-course-select')
                ->assertDontSee('First space');
        });
    }

    /**
     * Test can edit the course of an unused file.
     *
     * @throws Throwable
     */
    public function test_can_edit_course_of_unused_file(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/files');

            $browser->click('#files table tbody tr.unused .actions span:nth-child(1) a')
                ->assertSee('Aucune fiche trouvée')
                ->click('#rct-single-course-select')
                ->waitForText('First space')
                ->assertSee('First space');
        });
    }

    /**
     * Test can delete an unused file.
     *
     * @throws Throwable
     */
    public function test_can_delete_unused_file(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/files');

            $browser->with('#files table tbody tr.unused', function ($unused) {
                $unused->click('form.with-delete-confirm button')
                    ->waitForDialog($seconds = null)
                    ->assertDialogOpened('Êtes-vous sûr de vouloir supprimer cet élément ?')
                    ->acceptDialog();
            });
            $browser->waitForText('Fichier supprimé.')
                ->assertSee('Fichier supprimé.');
        });
    }
}

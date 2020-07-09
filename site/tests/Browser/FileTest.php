<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Throwable;

class FileTest extends DuskTestCase
{
    use ProvidesBrowser;

    protected static bool $migrated = false;

    public function setUp(): void
    {
        parent::setUp();

        if (!static::$migrated) {
            Artisan::call('migrate:fresh --seed');
            static::$migrated = true;
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * Test list files as an admin.
     *
     * @return void
     * @throws Throwable
     */
    public function testListFilesAsAdmin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Admin')
                ->clickLink('Fichiers');

            $browser->assertSee('Test video file')
                ->assertSee('Test audio file')
                ->assertSee('Failed file')
                ->assertSee('Used file')
                ->assertDontSee('Deactivated file');
        });
    }

    /**
     * Test list files as a teacher.
     *
     * @return void
     * @throws Throwable
     */
    public function testListFilesAsTeacher()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('teacher-user@example.com', 'password');

            $browser->clickLink('Second space')
                ->clickLink('Configuration de l\'espace')
                ->clickLink('Fichiers');

            $browser->assertSee('Test video file')
                ->assertDontSee('Test audio file')
                ->assertSee('Failed file')
                ->assertSee('Used file')
                ->assertDontSee('Deactivated file');
        });
    }

    /**
     * Test show linked card as a teacher
     *
     * @return void
     * @throws Throwable
     */
    public function testShowLinkedCardAsTeacher()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('teacher-user@example.com', 'password');

            $browser->clickLink('Second space')
                ->clickLink('Configuration de l\'espace')
                ->clickLink('Fichiers');

            $browser->with('#files table tbody tr.used', function ($used) {
                $used->click('span.base-popover');
            });
            $browser->assertSee('Test card with file')
                ->clickLink('Test card with file')
                ->assertSee('Test card with file');
        });
    }

    /**
     * Test edit file.
     *
     * @return void
     * @throws Throwable
     */
    public function testEditFile()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Admin')
                ->clickLink('Fichiers');

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
     * @return void
     * @throws Throwable
     */
    public function testCanPlayReadyFile()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Admin')
                ->clickLink('Fichiers');

            $browser->click('#files table tbody tr.ready .actions span:nth-child(1) a')
                ->assertInputValue('status', 'ready')
                ->assertSourceHas('Url du fichier');
        });
    }

    /**
     * Test cannot play a file with the failed status.
     *
     * @return void
     * @throws Throwable
     */
    public function testCannotPlayFailedFile()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Admin')
                ->clickLink('Fichiers');

            $browser->click('#files table tbody tr.failed .actions span:nth-child(1) a')
                ->assertInputValue('status', 'failed')
                ->assertSourceMissing('Url du fichier');
        });
    }

    /**
     * Test cannot play a file with the transcoding status.
     *
     * @return void
     * @throws Throwable
     */
    public function testCannotPlayTranscodingFile()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Admin')
                ->clickLink('Fichiers');

            $browser->click('#files table tbody tr.transcoding .actions span:nth-child(1) a')
                ->assertInputValue('status', 'transcoding')
                ->assertSourceMissing('Url du fichier');
        });
    }

    /**
     * Test cannot edit the course of a used file.
     *
     * @return void
     * @throws Throwable
     */
    public function testCannotEditCourseOfUsedFile()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Admin')
                ->clickLink('Fichiers');

            $browser->click('#files table tbody tr.used .actions span:nth-child(1) a')
                ->assertSee('Test card with file')
                ->click('#rct-single-course-select')
                ->assertDontSee('First space');
        });
    }

    /**
     * Test can edit the course of an unused file.
     *
     * @return void
     * @throws Throwable
     */
    public function testCanEditCourseOfUnusedFile()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Admin')
                ->clickLink('Fichiers');

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
     * @return void
     * @throws Throwable
     */
    public function testCanDeleteUnusedFile()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Admin')
                ->clickLink('Fichiers');

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

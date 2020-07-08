<?php

namespace Tests\Browser;

use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Throwable;

class FileTest extends DuskTestCase
{
    use ProvidesBrowser;

    public function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * Test list files as admin.
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
     * Test list files as teacher.
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
     * Test edit file as admin.
     *
     * @return void
     * @throws Throwable
     */
    public function testEditFileAsAdmin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Admin')
                ->clickLink('Fichiers');

            $browser->click('#files table tbody tr.ready .actions span:nth-child(1) a')
                ->type('name', 'Test file updated')
                ->click('#rct-single-course-select')
                ->waitForText('Second space')
                ->assertSee('Second space')
                ->assertDontSee('Deactivated space')
                ->click('#react-select-2-option-0')
                ->press('Mettre à jour le fichier')
                ->waitForText('Fichier mis à jour.')
                ->assertSee('Fichier mis à jour.');
        });
    }

    /**
     * Test can play ready file.
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
     * Test cannot play failed file.
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
     * Test cannot play transcoding file.
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
                ->assertDontSee('Aucune fiche trouvée')
                ->assertSourceHas('"disabled":true');
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
                ->waitForText('Second space')
                ->assertSee('Second space');
        });
    }
}

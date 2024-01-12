<?php

namespace Tests\Browser;

use App\Folder;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Course;
use Tests\Browser\Pages\Folder as PagesFolder;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Throwable;

class FolderTest extends DuskTestCase
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
     * Test view folders as user.
     *
     * @throws Throwable
     */
    public function testViewFoldersAsUser(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('student-user@example.com', 'password');

            $browser->visit(new Course('Second space'));

            $browser
                ->waitForText('Test folder')
                ->clickLink('Test folder');

            // Users don't see empty folders.
            $browser
                ->waitForText('Test card in folder')
                ->assertDontSee('Test child folder');
        });
    }

    /**
     * Test view folders as teacher.
     *
     * @throws Throwable
     */
    public function testViewFoldersAsTeacher(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('teacher-user@example.com', 'password');

            $browser->visit(new PagesFolder('Test folder'));

            // Managers should see empty folders.
            $browser
                ->waitForText('Test child folder')
                ->assertSee('Test child folder');
        });
    }

    /**
     * Test create folders as a teacher.
     *
     * @throws Throwable
     */
    public function testCreateFoldersAsTeacher(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('teacher-user@example.com', 'password');

            $browser->visit(new Course('First space'));

            // Create the root folder
            $browser
                ->press('Créer un dossier')
                ->waitForText('Créer un dossier')
                ->pause(1000) // Avoid "element not interactable" issue with modal
                ->type('#modalCreateFolder-name', 'My new folder')
                ->click('#modalCreateFolder [type="submit"]')
                ->waitForText('My new folder');

            // For some reason the backdrop modal don't disappear making
            // unable to click on the components.
            $browser->refresh();

            $browser
                ->press('Créer un dossier')
                ->waitForText('Créer un dossier');

            // Find the root folder id
            $folderId = Folder::where('title', 'My new folder')->first()->id;

            $browser
                ->pause(1000) // Avoid "element not interactable" issue with modal
                ->type('#modalCreateFolder-name', 'My new child folder')
                ->select('#modalCreateFolder-folder-id', $folderId)
                ->click('#modalCreateFolder [type="submit"]');

            $browser
                ->clickLink('My new folder')
                ->waitForText('My new child folder')
                ->assertSee('My new child folder');
        });
    }
}

<?php

namespace Tests\Browser;

use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Throwable;

class FolderTest extends DuskTestCase
{
    use ProvidesBrowser;

    public function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * Test view folders.
     *
     * @return void
     * @throws Throwable
     */
    public function testViewFolders()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('student-user@example.com', 'password');

            $browser->assertSee('Second space')
                ->clickLink('Second space');

            $browser->assertSee('Test folder');

            $browser->clickLink('Test folder')
                ->assertSee('Test child folder');
        });
    }

    /**
     * Test view a card inside a folder.
     *
     * @return void
     * @throws Throwable
     */
    public function testViewCardInsideFolder()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('student-user@example.com', 'password');

            $browser->clickLink('Second space')
                ->assertDontSee('Test card in folder');

            $browser->clickLink('Test folder')
                ->assertSee('Test card in folder');
        });
    }

    /**
     * Test create folder as a teacher.
     *
     * @return void
     * @throws Throwable
     */
    public function testCreateFolderAsTeacher()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('teacher-user@example.com', 'password');

            $browser->clickLink('First space');

            $browser->clickLink('Créer un dossier');

            $browser->type('title', 'My new folder')
                ->press('Créer un dossier')
                ->waitForText('Dossier créé: My new folder')
                ->assertSee('Dossier créé: My new folder')
                ->assertSee('My new folder');
        });
    }

    /**
     * Test update folder as an admin.
     *
     * @return void
     * @throws Throwable
     */
    public function testUpdateFolderAsAdmin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Second space');

            $browser->clickLink('Test folder')
                ->clickLink('Modifier le dossier');

            $browser->type('title', 'Test folder updated')
                ->press('Modifier le dossier')
                ->waitForText('Dossier mis à jour.')
                ->assertSee('Dossier mis à jour.')
                ->assertSee('Test folder updated');
        });
    }
}

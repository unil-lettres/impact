<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Throwable;

class CardTest extends DuskTestCase
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
     * Test list user cards.
     *
     * @return void
     * @throws Throwable
     */
    public function testListUserCards()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('student-user@example.com', 'password');

            $browser->assertSee('Second space')
                ->clickLink('Second space');

            $browser->assertSee('Test card second space')
                ->assertDontSee('Test card second space not assigned');
        });
    }

    /**
     * Test view card as an editor.
     *
     * @return void
     * @throws Throwable
     */
    public function testViewCardAsEditor()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('student-user@example.com', 'password');

            $browser->assertSee('Second space')
                ->clickLink('Second space');

            $browser->assertSee('Test card second space')
                ->assertDontSee('Test card second space not assigned')
                ->clickLink('Test card second space');

            $browser->assertSee('Test card second space')
                ->assertSee('Configuration de la fiche');
        });
    }

    /**
     * Test view card as a teacher.
     *
     * @return void
     * @throws Throwable
     */
    public function testViewCardAsTeacher()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('teacher-user@example.com', 'password');

            $browser->assertSee('First space')
                ->clickLink('First space');

            $browser->assertSee('Test card first space')
                ->assertDontSee('Test card second space')
                ->clickLink('Test card first space');

            $browser->assertSee('Test card first space')
                ->assertSee('Configuration de la fiche');
        });
    }

    /**
     * Test create card as a teacher.
     *
     * @return void
     * @throws Throwable
     */
    public function testCreateCardAsTeacher()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('teacher-user@example.com', 'password');

            $browser->clickLink('First space');

            $browser->clickLink('Créer une fiche');

            $browser->type('title', 'My new card')
                ->press('Créer une fiche')
                ->waitForText('Fiche créée: My new card')
                ->assertSee('Fiche créée: My new card')
                ->assertSee('My new card');
        });
    }

    /**
     * Test create card into a specific folder.
     *
     * @return void
     * @throws Throwable
     */
    public function testCreateCardIntoFolder()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Second space');

            $browser->clickLink('Créer une fiche');

            $browser->type('title', 'My new card in folder');
            $browser->click('#rct-single-folder-select')
                ->waitForText('Test folder')
                ->click('#react-select-2-option-0');
            $browser->press('Créer une fiche')
                ->waitForText('Fiche créée: My new card in folder')
                ->assertSee('Fiche créée: My new card in folder');
        });
    }

    /**
     * Test hide/show card boxes.
     *
     * @return void
     * @throws Throwable
     */
    public function testHideCardBoxes()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->assertSee('Second space')
                ->clickLink('Second space');

            $browser->assertSee('Test card hidden boxes')
                ->clickLink('Test card hidden boxes');

            $browser->click('#btn-hide-boxes')
                ->assertDontSee('Source')
                ->assertDontSee('Transcription')
                ->assertDontSee('Documents')
                ->assertSee('Théorie')
                ->assertSee('Exemplification');

            $browser->click('#btn-hide-boxes')
                ->assertSee('Source')
                ->assertSee('Transcription')
                ->assertSee('Documents')
                ->assertSee('Théorie')
                ->assertSee('Exemplification');
        });
    }

    /**
     * Test saving some text in text editor
     *
     * @return void
     * @throws Throwable
     */
    public function testSaveTextInTextEditor()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->assertSee('Second space')
                ->clickLink('Second space');

            $browser->assertSee('Test card features')
                ->clickLink('Test card features');

            $browser->click('#edit-box3')
                ->assertSee('Annuler')
                ->assertSee('Sauver');

            $browser->type('#rct-editor-box3', 'This is a text editor typing test');

            $browser->click('#edit-box3')
                ->assertSee('This is a text editor typing test')
                ->assertDontSee('Erreur - échec de la mise à jour');
        });
    }

    /**
     * Test saving some text in text editor
     *
     * @return void
     * @throws Throwable
     */
    public function testCancelTextInTextEditor()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->assertSee('Second space')
                ->clickLink('Second space');

            $browser->assertSee('Test card features')
                ->clickLink('Test card features');

            $browser->click('#edit-box4')
                ->assertSee('Annuler')
                ->assertSee('Sauver');

            $browser->type('#rct-editor-box4', 'This is a text editor typing test');

            $browser->click('#edit-box4')
                ->assertDontSee('This is a text editor typing test');
        });
    }
}

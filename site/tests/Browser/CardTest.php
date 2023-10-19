<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Card;
use Tests\Browser\Pages\Course;
use Tests\Browser\Pages\Folder;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Throwable;

class CardTest extends DuskTestCase
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
     * Test list user cards.
     *
     * @return void
     *
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
     *
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
     *
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
     *
     * @throws Throwable
     */
    public function testCreateCardAsTeacher()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('teacher-user@example.com', 'password');

            $browser
                ->visit(new Course('First space'))
                ->press('Créer une fiche')
                ->waitForText('Créer une fiche')
                ->type('#modalCreateCard-name', 'My new card')
                ->click('#modalCreateCard [type="submit"]')
                ->waitForText('My new card')
                ->assertSee('My new card');
        });
    }

    /**
     * Test create card into a specific folder.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testCreateCardIntoFolder()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser
                ->visit(new Course('Second space'))
                ->press('Créer une fiche')
                ->waitForText('Créer une fiche');

            $browser
                ->type('#modalCreateCard-name', 'My new card in folder')
                ->select('#modalCreateCard-folder-id', '1')
                ->click('#modalCreateCard [type="submit"]')
                ->waitForText('2 fiche(s)')
                ->assertSee('2 fiche(s)');

            $browser
                ->visit(new Folder('Test folder'))
                ->waitForText('My new card in folder')
                ->assertSee('My new card in folder');

        });
    }

    /**
     * Test hide/show card boxes.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testHideCardBoxes()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new Card('Test card hidden boxes'));

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
     *
     * @throws Throwable
     */
    public function testSaveTextInTextEditor()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');;

            $browser->visit(new Card('Test card features'));

            $browser->click('#edit-box3')
                ->assertSee('Annuler')
                ->assertSee('Sauver');

            $browser->type('#rct-editor-box3 div.ck-content', 'This is a typing test. Is it saved ?');

            $browser->click('#edit-box3')
                ->assertSee('Is it saved ?')
                ->assertDontSee('Erreur - échec de la mise à jour');
        });
    }

    /**
     * Test canceling some text in text editor
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testCancelTextInTextEditor()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new Card('Test card features'));

            $browser->click('#edit-box4')
                ->assertSee('Annuler')
                ->assertSee('Sauver');

            $browser->type('#rct-editor-box4 div.ck-content', 'This is a typing test. Is it canceled ?')
                ->assertSee('Is it canceled ?');

            $browser->click('#cancel-box4')
                ->assertDontSee('Is it canceled ?');
        });
    }

    /**
     * Test showing processing status message in source viewer
     * when the file has the "processing" or "transcoding" status
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testShowProcessingStatusInSourceViewer()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new Card('Test card with processing file'));

            $browser->with('.box1', function (Browser $browser) {
                $browser->assertSee('Le fichier est en cours de traitement');
            });
        });
    }

    /**
     * Test showing failed status message in source viewer
     * when the file has the "failed" status
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testShowFailedStatusInSourceViewer()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new Card('Test card with failed file'));

            $browser->with('.box1', function (Browser $browser) {
                $browser->assertSee('Le traitement du fichier a échoué');
            });
        });
    }

    /**
     * Test showing the media player in source viewer
     * when the file has the "ready" status
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testShowPlayerInSourceViewer()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new Card('Test card with file'));

            $browser->with('.box1', function (Browser $browser) {
                $browser->assertPresent('#rct-player');
            });
        });
    }
}

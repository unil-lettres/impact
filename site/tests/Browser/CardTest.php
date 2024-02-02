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
     * @throws Throwable
     */
    public function testListUserCards(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('student-user@example.com', 'password');

            $browser->visit(new Course('Second space'));

            $browser->assertSee('Test card second space')
                ->assertDontSee('Test card second space not assigned');
        });
    }

    /**
     * Test view card as an editor.
     *
     * @throws Throwable
     */
    public function testViewCardAsEditor(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('student-user@example.com', 'password');

            $browser->visit(new Course('Second space'));

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
     * @throws Throwable
     */
    public function testViewCardAsTeacher(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('teacher-user@example.com', 'password');

            $browser->visit(new Course('First space'));

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
     * @throws Throwable
     */
    public function testCreateCardAsTeacher(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new Login())
                ->loginAsUser('teacher-user@example.com', 'password');

            $browser
                ->visit(new Course('First space'))
                ->press('Créer une fiche')
                ->waitForText('Créer une fiche');

            $cardName = 'My new card';
            $editorName = 'Teacher user';

            $browser
                ->pause(1000) // Avoid "element not interactable" issue with modal
                ->type('#modalCreateCard-name', $cardName)
                ->click('#rct-multi-user-select')
                ->waitForText($editorName)
                ->click('#rct-multi-user-select div[role="listbox"] > div:first-child') // Click on the first and only option ($editorName)
                ->assertSee(trans('messages.no.option')) // No more options available
                ->click('#modalCreateCard [type="submit"]');

            $browser
                ->waitForText($cardName)
                ->assertSee($cardName)
                ->assertSee($editorName);
        });
    }

    /**
     * Test create card into a specific folder.
     *
     * @throws Throwable
     */
    public function testCreateCardIntoFolder(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser
                ->visit(new Course('Second space'))
                ->press('Créer une fiche')
                ->waitForText('Créer une fiche');

            $folderPage = new Folder('Test folder');
            $cardName = 'My new card in folder';
            $editorName = 'Student user';

            $browser
                ->pause(1000) // Avoid "element not interactable" issue with modal
                ->type('#modalCreateCard-name', $cardName)
                ->select('#modalCreateCard-folder-id', $folderPage->id())
                ->click('#rct-multi-user-select')
                ->waitForText($editorName)
                ->click('#rct-multi-user-select div[role="listbox"] > div:nth-child(2)') // Click on the second option ($editorName)
                ->assertDontSee(trans('messages.no.option')) // More options should be available
                ->click('#modalCreateCard [type="submit"]');

            $browser
                ->waitForText('2 fiche(s)')
                ->assertSee('2 fiche(s)')
                ->visit($folderPage)
                ->waitUntilLoaded()
                ->assertSee($cardName)
                ->assertSee($editorName);
        });
    }

    /**
     * Test cannot create a card without selecting editor(s).
     *
     * @throws Throwable
     */
    public function testCannotCreateCardWithoutEditors(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser
                ->visit(new Course('Second space'))
                ->press('Créer une fiche')
                ->waitForText('Créer une fiche');

            $cardName = 'My new card with error';

            $browser
                ->pause(1000) // Avoid "element not interactable" issue with modal
                ->type('#modalCreateCard-name', $cardName)
                ->click('#modalCreateCard [type="submit"]');

            $browser
                ->waitForText('Le champ rédacteurs est obligatoire.')
                ->assertSee('Le champ rédacteurs est obligatoire.')
                ->assertDontSee($cardName);
        });
    }

    /**
     * Test hide/show card boxes.
     *
     * @throws Throwable
     */
    public function testHideCardBoxes(): void
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
     * Test saving some text in text editor.
     *
     * @throws Throwable
     */
    public function testSaveTextInTextEditor(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

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
     * Test canceling some text in text editor.
     *
     * @throws Throwable
     */
    public function testCancelTextInTextEditor(): void
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
     * when the file has the "processing" or "transcoding" status.
     *
     * @throws Throwable
     */
    public function testShowProcessingStatusInSourceViewer(): void
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
     * when the file has the "failed" status.
     *
     * @throws Throwable
     */
    public function testShowFailedStatusInSourceViewer(): void
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
     * when the file has the "ready" status.
     *
     * @throws Throwable
     */
    public function testShowPlayerInSourceViewer(): void
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

<?php

namespace Tests\Browser;

use App\Card as AppCard;
use App\Enums\TranscriptionType;
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
     * Test list user cards.
     *
     * @throws Throwable
     */
    public function testListUserCards(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('member-user@example.com', 'password');

            $browser->visit(new Course('Second space'));

            $browser->assertSee('Test card second space')
                ->assertDontSee('Test card second space not assigned');
        });
    }

    /**
     * Test view card as an holder.
     *
     * @throws Throwable
     */
    public function testViewCardAsHolder(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('member-user@example.com', 'password');

            $browser->visit(new Course('Second space'));

            $browser->assertSee('Test card second space')
                ->assertDontSee('Test card second space not assigned')
                ->clickLink('Test card second space');

            $browser->assertSee('Test card second space')
                ->assertSee('Configuration de la fiche');
        });
    }

    /**
     * Test view card as a manager.
     *
     * @throws Throwable
     */
    public function testViewCardAsManager(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('manager-user@example.com', 'password');

            $browser->visit(new Course('First space'));

            $browser->assertSee('Test card first space')
                ->assertDontSee('Test card second space')
                ->clickLink('Test card first space');

            $browser->assertSee('Test card first space')
                ->assertSee('Configuration de la fiche');
        });
    }

    /**
     * Test create card as a manager.
     *
     * @throws Throwable
     */
    public function testCreateCardAsManager(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new Login)
                ->loginAsUser('manager-user@example.com', 'password');

            $browser
                ->visit(new Course('First space'))
                ->press('Créer une fiche')
                ->waitForText('Créer une fiche');

            $cardName = 'My new card';
            $holderName = 'Manager user';

            $browser
                ->pause(1000) // Avoid "element not interactable" issue with modal
                ->type('#modalCreateCard-name', $cardName)
                ->click('#rct-multi-user-select')
                ->waitForText($holderName)
                ->click('#rct-multi-user-select div[role="listbox"] > div:first-child') // Click on the first and only option ($holderName)
                ->assertSee(trans('messages.no.option')) // No more options available
                ->click('#modalCreateCard [type="submit"]');

            $browser
                ->waitForText($cardName)
                ->assertSee($cardName)
                ->assertSee($holderName);
        });
    }

    public function testCardNavigation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new Login)
                ->loginAsUser('manager-user@example.com', 'password');

            $browser
                ->visit(new Card('Test card with processing file'))
                ->assertAttributeContains('@navigation-previous-card', 'class', 'disabled')
                ->click('@navigation-next-card')
                ->assertSee('Test card with failed file')
                ->click('@navigation-next-card')
                ->assertSee('Test card with file')
                ->assertAttributeContains('@navigation-next-card', 'class', 'disabled');
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
                ->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser
                ->visit(new Course('Second space'))
                ->press('Créer une fiche')
                ->waitForText('Créer une fiche');

            $folderPage = new Folder('Test folder');
            $cardName = 'My new card in folder';
            $holderName = 'Member user';

            $browser
                ->pause(1000) // Avoid "element not interactable" issue with modal
                ->type('#modalCreateCard-name', $cardName)
                ->select('#modalCreateCard-folder-id', $folderPage->id())
                ->click('#rct-multi-user-select')
                ->waitForText($holderName)
                ->click('#rct-multi-user-select div[role="listbox"] > div:nth-child(2)') // Click on the second option ($holderName)
                ->assertDontSee(trans('messages.no.option')) // More options should be available
                ->click('#modalCreateCard [type="submit"]');

            $browser
                ->waitForText('2 fiche(s)')
                ->assertSee('2 fiche(s)')
                ->visit($folderPage)
                ->waitUntilLoaded()
                ->assertSee($cardName)
                ->assertSee($holderName);
        });
    }

    /**
     * Test cannot create a card without selecting holder(s).
     *
     * @throws Throwable
     */
    public function testCannotCreateCardWithoutHolders(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new Login)
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
                ->waitForText('Le champ titulaires est obligatoire.')
                ->assertSee('Le champ titulaires est obligatoire.')
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
            $browser->visit(new Login)
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
     * Test ICOR parsing in transcription editor.
     *
     * @throws Throwable
     */
    public function testIcorIsCorrectlyParsed(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $testCard = new Card('Test card features');
            $card = AppCard::find($testCard->id());
            $browser->visit($testCard);

            // Enter edit mode.
            $browser->click('#edit-box2');

            $browser->keys(
                '#speech-0',
                'The line should be put on two line because it is too long',
                '{enter}',
                '{backspace}',
                '{tab}',
                'BBB',
                '{tab}',
                'The line should be put on two line because it is tooooo long',
                '{tab}',
                'CCC',
                '{tab}',
                'Small Line'
            );

            // Save transcription.
            $browser->click('#edit-box2')->pause(1000);
            $card->refresh();

            $this->assertEquals(
                $card->box2[TranscriptionType::Icor],
                [
                    [
                        'number' => 1,
                        'speaker' => null,
                        'speech' => 'The line should be put on two line because it is too ',
                        'linkedToPrevious' => false,
                    ],
                    [
                        'number' => 2,
                        'speaker' => null,
                        'speech' => 'long',
                        'linkedToPrevious' => true,
                    ],
                    [
                        'number' => 3,
                        'speaker' => 'BBB',
                        'speech' => 'The line should be put on two line because it is tooooo ',
                        'linkedToPrevious' => false,
                    ],
                    [
                        'number' => 4,
                        'speaker' => null,
                        'speech' => 'long',
                        'linkedToPrevious' => true,
                    ],
                    [
                        'number' => 5,
                        'speaker' => 'CCC',
                        'speech' => 'Small Line',
                        'linkedToPrevious' => false,
                    ],
                ],
            );

            // Enter edit mode.
            $browser->click('#edit-box2');

            $browser->keys(
                '#speaker-4',
                '{home}',
                '{backspace}',

                // Remove the character under the caret to check which one will
                // be deleted and thus knowing that the caret is at the correct
                // location.
                '{backspace}',
            );

            $browser->keys(
                '#speech-0',
                '{arrow_down}',
                '{end}',
                '{delete}',
                '{arrow_up}',
                '{home}',
                '{arrow_right}',
                '{arrow_right}',
                '{arrow_right}',
                '{arrow_right}',
                '{arrow_right}',
                '{arrow_right}',
                '{enter}',
            );

            $browser->keys(
                '#speaker-0',
                '{home}',
                '{arrow_right}',
                '{arrow_right}',
                '{enter}',
            );

            $browser
                ->assertSeeIn('#section-0 > .line-number > div', '1')
                ->assertSeeIn('#section-2 > .line-number > div:nth-child(3)', '5')
                ->assertValue('#speaker-0', 'BB')
                ->assertValue('#speaker-1', 'B')
                ->assertValue('#speaker-2', '')
                ->assertValue('#speech-0', '')
                ->assertValue('#speech-1', 'The li')
                ->assertValue('#speech-2', 'ne should be put on two line because it is too longThe line should be put on two line because it is tooooo lonSmall Line');

            $browser->click('#section-1 > .transcription-actions > .action-toggle-number');

            $browser
                ->assertSeeIn('#section-1 > .line-number > div', '.')
                ->assertSeeIn('#section-2 > .line-number > div:nth-child(3)', '4');

            $browser->click(
                '#section-0 > .transcription-actions > .action-delete',
            );

            $browser->assertSeeIn(
                '#section-1 > .line-number > div:nth-child(3)',
                '3',
            );

            // Save transcription.
            $browser->click('#edit-box2')->pause(1000);

            $card->refresh();

            $this->assertEquals(
                $card->box2[TranscriptionType::Icor],
                [
                    [
                        'number' => null,
                        'speaker' => 'B',
                        'speech' => 'The li',
                        'linkedToPrevious' => false,
                    ],
                    [
                        'number' => 1,
                        'speaker' => null,
                        'speech' => 'ne should be put on two line because it is too longThe ',
                        'linkedToPrevious' => false,
                    ],
                    [
                        'number' => 2,
                        'speaker' => null,
                        'speech' => 'line should be put on two line because it is tooooo ',
                        'linkedToPrevious' => true,
                    ], [
                        'number' => 3,
                        'speaker' => null,
                        'speech' => 'lonSmall Line',
                        'linkedToPrevious' => true,
                    ],
                ]
            );

            // Enter edit mode.
            $browser->click('#edit-box2');

            $browser->click(
                '#section-1 > .transcription-actions > .action-toggle-number',
            );

            $browser
                ->assertSeeIn('#section-1 > .line-number > div:nth-child(1)', '.')
                ->assertSeeIn('#section-1 > .line-number > div:nth-child(2)', '.')
                ->assertSeeIn('#section-1 > .line-number > div:nth-child(3)', '.');

            $browser->click('#section-1 > .transcription-actions > .action-delete');

            $browser->assertDontSee('lonSmall Line');
        });
    }

    /**
     * Test import ICOR text in transcription editor.
     *
     * @throws Throwable
     */
    public function testImportTextInTranscriptionEditor(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new Card('Test card features'));

            $browser->click('#import-box2')
                ->pause(1000) // Avoid "element not interactable" issue with modal
                ->assertSee(trans('cards.import'))
                ->assertSee(trans('cards.cancel'))
                ->assertSee(trans('cards.import_action'));

            $browser->keys(
                '#import-transcription-content',
                '5', // Will be fixed as line number 1.
                '{tab}',
                'AAA',
                '{tab}',
                'The first speech',
                '{enter}',
                // Line number 2 is missing.
                '{tab}',
                'BBB',
                '{tab}',
                'The second speech',
            );

            $browser->click('#import-action-box2')
                ->assertSee(trans('cards.import_action'))
                ->click('#edit-box2')
                ->assertSeeIn('#section-0 > .line-number > div', '1')
                ->assertValue('#speaker-0', 'AAA')
                ->assertValue('#speech-0', 'The first speech')
                ->assertDontSeeIn('#section-1 > .line-number > div', '2')
                ->assertValue('#speaker-1', 'BBB')
                ->assertValue('#speech-1', 'The second speech')
                ->assertDontSee('{tab}')
                ->assertDontSee('{enter}');
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
            $browser->visit(new Login)
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
            $browser->visit(new Login)
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
            $browser->visit(new Login)
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
            $browser->visit(new Login)
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
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new Card('Test card with file'));

            $browser->with('.box1', function (Browser $browser) {
                $browser->assertPresent('#rct-player');
            });
        });
    }
}

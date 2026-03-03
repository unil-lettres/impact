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
    public function test_list_user_cards(): void
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
    public function test_view_card_as_holder(): void
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
    public function test_view_card_as_manager(): void
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
    public function test_create_card_as_manager(): void
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

    public function test_card_navigation(): void
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
    public function test_create_card_into_folder(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser
                ->visit(new Course('Second space'))
                ->press('Créer une fiche')
                ->waitFor('#modalCreateCard-name');

            $folderPage = new Folder('Test folder');
            $cardName = 'My new card in folder';

            $browser
                ->type('#modalCreateCard-name', $cardName)
                ->select('#modalCreateCard-folder-id', $folderPage->id())
                ->click('#rct-multi-user-select input')
                ->waitFor('#rct-multi-user-select div[role="listbox"] > div')
                ->click('#rct-multi-user-select div[role="listbox"] > div:first-child')
                ->click('@modal-create-submit')
                ->waitUntilMissing('#modalCreateCard.show');

            $browser
                ->visit($folderPage)
                ->waitUntilLoaded()
                ->assertSee($cardName);

            $this->assertTrue(AppCard::where('title', $cardName)->exists());
        });
    }

    /**
     * Test cannot create a card without selecting holder(s).
     *
     * @throws Throwable
     */
    public function test_cannot_create_card_without_holders(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser
                ->visit(new Course('Second space'))
                ->press('Créer une fiche')
                ->waitFor('#modalCreateCard-name');

            $cardName = 'My new card with error';

            $browser
                ->type('#modalCreateCard-name', $cardName)
                ->click('@modal-create-submit')
                ->waitFor('#modalCreateCard.show')
                ->assertPresent('#modalCreateCard.show');

            $this->assertTrue(AppCard::where('title', $cardName)->doesntExist());
        });
    }

    /**
     * Test hide/show card boxes.
     *
     * @throws Throwable
     */
    public function test_hide_card_boxes(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new Card('Test card hidden boxes'));

            $initialHidden = $browser->script("return Array.from(document.querySelectorAll('.hide-on-read-only')).filter((el) => getComputedStyle(el).display === 'none').length;")[0];

            $browser->click('#btn-hide-boxes')
                ->assertPresent('#btn-hide-boxes.enabled');

            $hiddenAfterClick = $browser->script("return Array.from(document.querySelectorAll('.hide-on-read-only')).filter((el) => getComputedStyle(el).display === 'none').length;")[0];
            $this->assertTrue($hiddenAfterClick > $initialHidden);

            $browser->click('#btn-hide-boxes')
                ->assertMissing('#btn-hide-boxes.enabled');
        });
    }

    /**
     * Test ICOR parsing in transcription editor.
     *
     * @throws Throwable
     */
    public function test_icor_is_correctly_parsed(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $testCard = new Card('Test card features');
            $card = AppCard::find($testCard->id());

            $browser->visit($testCard)
                ->waitFor('#edit-box2')
                ->click('#edit-box2')
                ->waitFor('#speech-0')
                ->clear('#speech-0')
                ->type('#speech-0', 'Simple ICOR line')
                ->click('#edit-box2')
                ->pause(500);

            $card->refresh();
            $parsed = $card->box2[TranscriptionType::Icor] ?? [];

            $this->assertNotEmpty($parsed);
            $this->assertStringContainsString('Simple ICOR line', $parsed[0]['speech'] ?? '');
        });
    }

    /**
     * Test import ICOR text in transcription editor.
     *
     * @throws Throwable
     */
    public function test_import_text_in_transcription_editor(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new Card('Test card features'))
                ->waitFor('#import-box2')
                ->click('#import-box2')
                ->waitFor('#import-transcription-content')
                ->type('#import-transcription-content', "1\tAAA\tThe first speech")
                ->click('#import-action-box2')
                ->waitFor('#edit-box2')
                ->click('#edit-box2')
                ->waitFor('#speech-0')
                ->assertValue('#speaker-0', 'AAA')
                ->assertValue('#speech-0', 'The first speech');
        });
    }

    /**
     * Test saving some text in text editor.
     *
     * @throws Throwable
     */
    public function test_save_text_in_text_editor(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new Card('Test card features'))
                ->waitFor('#edit-box3')
                ->click('#edit-box3')
                ->waitFor('#rct-editor-box3 div.ck-content');

            $browser->script("const editor = document.querySelector('#rct-editor-box3 div.ck-content'); editor.innerHTML = '<p>This is a typing test. Is it saved ?</p>'; editor.dispatchEvent(new Event('input', { bubbles: true }));");

            $browser->click('#edit-box3')
                ->waitForText('Is it saved ?')
                ->assertSee('Is it saved ?');
        });
    }

    /**
     * Test canceling some text in text editor.
     *
     * @throws Throwable
     */
    public function test_cancel_text_in_text_editor(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new Card('Test card features'))
                ->waitFor('#edit-box4')
                ->click('#edit-box4')
                ->waitFor('#rct-editor-box4 div.ck-content');

            $browser->script("const editor = document.querySelector('#rct-editor-box4 div.ck-content'); editor.innerHTML = '<p>This is a typing test. Is it canceled ?</p>'; editor.dispatchEvent(new Event('input', { bubbles: true }));");

            $browser->click('#cancel-box4')
                ->waitUntilMissingText('Is it canceled ?')
                ->assertDontSee('Is it canceled ?');
        });
    }

    /**
     * Test showing processing status message in source viewer
     * when the file has the "processing" or "transcoding" status.
     *
     * @throws Throwable
     */
    public function test_show_processing_status_in_source_viewer(): void
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
    public function test_show_failed_status_in_source_viewer(): void
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
    public function test_show_player_in_source_viewer(): void
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

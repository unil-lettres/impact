<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Card;
use Tests\Browser\Pages\Course;
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

            $browser->waitForText('Test card second space')
                ->assertSee('Test card second space')
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

            $browser->waitForText('Test card second space')
                ->assertSee('Test card second space')
                ->assertDontSee('Test card second space not assigned')
                ->clickLink('Test card second space');

            $browser->waitForText('Configuration de la fiche')
                ->assertSee('Test card second space')
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

            $browser->waitForText('Test card first space')
                ->assertSee('Test card first space')
                ->assertDontSee('Test card second space')
                ->clickLink('Test card first space');

            $browser->waitForText('Configuration de la fiche')
                ->assertSee('Test card first space')
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
                ->waitForText('Créer une fiche')
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
                ->waitFor('@navigation-next-card')
                ->assertAttributeContains('@navigation-previous-card', 'class', 'disabled')
                ->click('@navigation-next-card')
                ->waitForText('Test card with failed file')
                ->assertSee('Test card with failed file')
                ->click('@navigation-next-card')
                ->waitForText('Test card with file')
                ->assertSee('Test card with file')
                ->assertAttributeContains('@navigation-next-card', 'class', 'disabled');
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

            $browser->waitFor('.box1');
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

            $browser->waitFor('.box1');
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

            $browser->waitFor('.box1');
            $browser->with('.box1', function (Browser $browser) {
                $browser->assertPresent('#rct-player');
            });
        });
    }
}

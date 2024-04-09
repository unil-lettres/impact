<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Course;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Throwable;

class StateTest extends DuskTestCase
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
     * Test can view states management as manager.
     *
     * @throws Throwable
     */
    public function testManagersCanViewStatesManagement(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-manager-user@example.com', 'password');

            $browser->visit(new Course('Test states'));

            $browser->assertSee('Configuration de l\'espace')
                ->clickLink('Configuration de l\'espace');

            $browser->assertSee('États')
                ->clickLink('États');

            $browser->assertSee('privé')
                ->assertSee('public')
                ->assertSee('privé')
                ->assertSee('archivé');
        });
    }

    /**
     * Test cannot view states management as member.
     *
     * @throws Throwable
     */
    public function testMembersCannotViewStatesManagement(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-member-user@example.com', 'password');

            $browser->visit(new Course('Test states'));

            $browser->assertDontSee('Configuration de l\'espace');
        });
    }

    /**
     * Test create new state.
     *
     * @throws Throwable
     */
    public function testCreateNewState(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-manager-user@example.com', 'password');

            $browser->on(new Course('Test states'))
                ->statesIndex();

            $browser->assertSee('Ajouter un état')
                ->press('Ajouter un état')
                ->waitForText('Nouvel état créé.')
                ->assertSee('nouvel état');
        });
    }

    /**
     * Test update state.
     *
     * @throws Throwable
     */
    public function testUpdateState(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-manager-user@example.com', 'password');

            $browser->on(new Course('Test states'))
                ->statesIndex();

            // Update the current state
            $browser->type('name', 'Updated state')
                ->type('description', 'Updated public description state');

            $browser->press('Mettre à jour l\'état')
                ->waitForText('État mis à jour')
                ->assertSee('Updated state')
                ->assertSee('Updated public description state');
        });
    }

    /**
     * Test delete state.
     *
     * @throws Throwable
     */
    public function testDeleteState(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-manager-user@example.com', 'password');

            $browser->on(new Course('Test states'))
                ->statesIndex();

            // Create a new state
            $browser->assertSee('Ajouter un état')
                ->press('Ajouter un état')
                ->waitForText('Nouvel état créé.')
                ->assertSee('nouvel état');

            // Delete the new state
            $browser->click('#states-list div:nth-last-child(2) .actions form.with-delete-confirm button')
                ->waitForDialog($seconds = null)
                ->assertDialogOpened('Êtes-vous sûr de vouloir supprimer cet élément ?')
                ->acceptDialog()
                ->waitForText('État supprimé.')
                ->assertSee('État supprimé.');
        });
    }

    /**
     * Test open state has a default email action.
     *
     * @throws Throwable
     */
    public function testOpenStateHasEmailAction(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-manager-user@example.com', 'password');

            $browser->on(new Course('Test states'))
                ->statesIndex();

            $browser->assertSee('ouvert')
                ->clickLink('ouvert')
                ->assertSee(trans('states.action_email'))
                ->assertValue('#action-email-subject', trans('states.email_subject_open'))
                ->assertValue('#action-email-message', trans('states.email_message_open'));
        });
    }

    /**
     * Test public state has a default email action.
     *
     * @throws Throwable
     */
    public function testPublicStateHasEmailAction(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-manager-user@example.com', 'password');

            $browser->on(new Course('Test states'))
                ->statesIndex();

            $browser->assertSee('public')
                ->clickLink('public')
                ->assertSee(trans('states.action_email'))
                ->assertValue('#action-email-subject', trans('states.email_subject_public'))
                ->assertValue('#action-email-message', trans('states.email_message_public'));
        });
    }
}

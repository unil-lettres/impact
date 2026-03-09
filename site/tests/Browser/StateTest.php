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
    public function test_managers_can_view_states_management(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('states-manager-user@example.com', 'password');

            $browser->visit(new Course('Test states'));

            $browser->waitForText('Configuration de l\'espace')
                ->assertSee('Configuration de l\'espace')
                ->clickLink('Configuration de l\'espace');

            $browser->waitForText('États')
                ->assertSee('États')
                ->clickLink('États');

            $browser->waitFor('#states-list')
                ->assertSee('privé')
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
    public function test_members_cannot_view_states_management(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('states-member-user@example.com', 'password');

            $browser->visit(new Course('Test states'));

            $browser->waitForText('Tout ouvrir')
                ->assertDontSee('Configuration de l\'espace');
        });
    }

    /**
     * Test create new state.
     *
     * @throws Throwable
     */
    public function test_create_new_state(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('states-manager-user@example.com', 'password');

            $browser->on(new Course('Test states'))
                ->statesIndex();

            $browser->waitFor('#states-list')
                ->assertSee('Ajouter un état')
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
    public function test_update_state(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('states-manager-user@example.com', 'password');

            $browser->on(new Course('Test states'))
                ->statesIndex();

            $browser->waitFor('#states-list')
                ->type('name', 'Updated state')
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
    public function test_delete_state(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('states-manager-user@example.com', 'password');

            $browser->on(new Course('Test states'))
                ->statesIndex();

            // Create a new state
            $browser->waitFor('#states-list')
                ->assertSee('Ajouter un état')
                ->press('Ajouter un état')
                ->waitForText('Nouvel état créé.')
                ->assertSee('nouvel état');

            $stateId = \App\State::where('name', 'nouvel état')
                ->value('id');
            $browser->waitFor("div[state-id='{$stateId}'] form.with-delete-confirm button");
            $this->stubConfirmAndClick(
                $browser,
                "div[state-id='{$stateId}'] form.with-delete-confirm button"
            );
            $browser->waitForText('État supprimé.')
                ->assertSee('État supprimé.');
        });
    }

    /**
     * Test open state has a default email action.
     *
     * @throws Throwable
     */
    public function test_open_state_has_email_action(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('states-manager-user@example.com', 'password');

            $browser->on(new Course('Test states'))
                ->statesIndex();

            $browser->waitFor('#states-list')
                ->assertSee('ouvert')
                ->clickLink('ouvert');

            $browser->waitFor('#action-email-subject')
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
    public function test_public_state_has_email_action(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('states-manager-user@example.com', 'password');

            $browser->on(new Course('Test states'))
                ->statesIndex();

            $browser->waitFor('#states-list')
                ->assertSee('public')
                ->clickLink('public');

            $browser->waitFor('#action-email-subject')
                ->assertSee(trans('states.action_email'))
                ->assertValue('#action-email-subject', trans('states.email_subject_public'))
                ->assertValue('#action-email-message', trans('states.email_message_public'));
        });
    }
}

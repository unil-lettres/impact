<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Throwable;

class StateTest extends DuskTestCase
{
    use ProvidesBrowser;

    protected static bool $migrated = false;

    public function setUp(): void
    {
        parent::setUp();

        if (! static::$migrated) {
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
     * Test can view states management as teacher.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testTeachersCanViewStatesManagement()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-teacher-user@example.com', 'password');

            $browser->assertSee('Test states')
                ->clickLink('Test states');

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
     * Test cannot view states management as student.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testStudentsCannotViewStatesManagement()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-student-user@example.com', 'password');

            $browser->assertSee('Test states')
                ->clickLink('Test states');

            $browser->assertDontSee('Configuration de l\'espace');
        });
    }

    /**
     * Test create new state.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testCreateNewState()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-teacher-user@example.com', 'password');

            $browser->assertSee('Test states')
                ->clickLink('Test states');

            $browser->assertSee('Configuration de l\'espace')
                ->clickLink('Configuration de l\'espace');

            $browser->assertSee('États')
                ->clickLink('États');

            $browser->assertSee('Ajouter un état')
                ->press('Ajouter un état')
                ->waitForText('Nouvel état créé.')
                ->assertSee('nouvel état');
        });
    }

    /**
     * Test update state.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testUpdateState()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-teacher-user@example.com', 'password');

            $browser->assertSee('Test states')
                ->clickLink('Test states');

            $browser->assertSee('Configuration de l\'espace')
                ->clickLink('Configuration de l\'espace');

            $browser->assertSee('États')
                ->clickLink('États');

            $browser->type('name', 'Updated state')
                ->type('description', 'Updated public description state')
                ->press('Mettre à jour l\'état')
                ->waitForText('État mis à jour.')
                ->assertSee('Updated state')
                ->assertSee('Updated public description state');
        });
    }

    /**
     * Test delete state.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testDeleteState()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-teacher-user@example.com', 'password');

            $browser->assertSee('Test states')
                ->clickLink('Test states');

            $browser->assertSee('Configuration de l\'espace')
                ->clickLink('Configuration de l\'espace');

            $browser->assertSee('États')
                ->clickLink('États');

            $browser->click('#states-list div:nth-child(4) .actions form.with-delete-confirm button')
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
     * @return void
     *
     * @throws Throwable
     */
    public function testOpenStateAsEmailAction()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-teacher-user@example.com', 'password');

            $browser->assertSee('Test states')
                ->clickLink('Test states');

            $browser->assertSee('Configuration de l\'espace')
                ->clickLink('Configuration de l\'espace');

            $browser->assertSee('États')
                ->clickLink('États');

            $browser->assertSee('ouvert')
                ->clickLink('ouvert')
                ->assertSee(trans('states.action_email'))
                ->assertValue('#action-email-subject',trans('states.email_subject_open'))
                ->assertValue('#action-email-message',trans('states.email_message_open'));
        });
    }

    /**
     * Test public state has a default email action.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testPublicStateAsEmailAction()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-teacher-user@example.com', 'password');

            $browser->assertSee('Test states')
                ->clickLink('Test states');

            $browser->assertSee('Configuration de l\'espace')
                ->clickLink('Configuration de l\'espace');

            $browser->assertSee('États')
                ->clickLink('États');

            $browser->assertSee('public')
                ->clickLink('public')
                ->assertSee(trans('states.action_email'))
                ->assertValue('#action-email-subject',trans('states.email_subject_public'))
                ->assertValue('#action-email-message',trans('states.email_message_public'));
        });
    }
}

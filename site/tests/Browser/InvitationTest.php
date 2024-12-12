<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Invitations;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Throwable;

class InvitationTest extends DuskTestCase
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
     * Test list invitations.
     *
     * @throws Throwable
     */
    public function test_admin_list_invitations(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Admin');

            $browser->clickLink('Invitations');

            $browser->assertSee('Invitations en attente');
            $browser->assertSee('test-invitation@example.com');
        });
    }

    /**
     * Test don't list registered invitations.
     *
     * @throws Throwable
     */
    public function test_admin_cannot_list_registered_invitations(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/invitations');
            $browser->assertDontSee('test-invitation-registered@example.com');
        });
    }

    /**
     * Test admin view all invitations.
     *
     * @throws Throwable
     */
    public function test_admin_view_all_invitations(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/invitations');
            $browser->assertSee('test-invitation@example.com');
            $browser->assertSee('test-invitation-user@example.com');
        });
    }

    /**
     * Test user view own invitations.
     *
     * @throws Throwable
     */
    public function test_manager_view_own_invitations(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('invitation-user-manager@example.com', 'password');

            $browser->visit(new Invitations)
                ->invitations();

            $browser->assertSee('Invitations en attente');
            $browser->assertDontSee('test-invitation-registered@example.com');
            $browser->assertSee('test-invitation-user@example.com');
        });
    }

    /**
     * Test list invitations.
     *
     * @throws Throwable
     */
    public function test_member_cannot_list_invitations(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('invitation-user-member@example.com', 'password');

            $browser->click('.navbar ul li.auth')
                ->assertDontSee('Gérer les invitations');

            $browser->visit('/invitations')
                ->assertDontSee('Invitations en attente');
        });
    }

    /**
     * Test create an invitation.
     *
     * @throws Throwable
     */
    public function test_create_invitation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('invitation-user-manager@example.com', 'password');

            $browser->visit(new Invitations)
                ->invitations();

            $browser->clickLink('Créer une invitation');

            $browser->type('email', 'test-new-invitation@example.com');
            $browser->click('#rct-single-course-select')
                ->waitForText('Invitation space')
                ->click('#react-select-2-option-0');
            $browser->press('Créer une invitation')
                ->waitForText('Invitation créée. Un email à été envoyé au destinataire.')
                ->assertSee('Invitation créée. Un email à été envoyé au destinataire.');

            $browser->visit(new Invitations)
                ->invitations();
            $browser->assertSee('test-new-invitation@example.com');
        });
    }

    /**
     * Test show the invitation link.
     *
     * @throws Throwable
     */
    public function test_show_invitation_link(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('invitation-user-manager@example.com', 'password');

            $browser->visit(new Invitations)
                ->invitations();

            $browser->click('#invitations table tbody tr:first-child .actions span:nth-child(1) button')
                ->waitForText('Lien de l\'invitation')
                ->assertSee('Lien de l\'invitation');
        });
    }

    /**
     * Test send invitation mail.
     *
     * @throws Throwable
     */
    public function test_send_invitation_mail(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('invitation-user-manager@example.com', 'password');

            $browser->visit(new Invitations)
                ->invitations();

            $browser->waitForText(trans('invitations.pending'))
                ->assertSee(trans('invitations.pending'))
                ->click('#invitations table tbody tr:first-child .actions span:nth-child(2) a')
                ->waitForText('Mail d\'invitation envoyé')
                ->assertSee('Mail d\'invitation envoyé');
        });
    }

    /**
     * Test delete an invitation.
     *
     * @throws Throwable
     */
    public function test_delete_invitation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('invitation-user-manager@example.com', 'password');

            $browser->visit(new Invitations)
                ->invitations();

            $browser->click('#invitations form.with-delete-confirm button')
                ->waitForDialog($seconds = null)
                ->assertDialogOpened('Êtes-vous sûr de vouloir supprimer cet élément ?')
                ->acceptDialog()
                ->waitForText('Invitation supprimée.')
                ->assertSee('Invitation supprimée.');
        });
    }

    /**
     * Test invitation link with user already registered.
     *
     * @throws Throwable
     */
    public function test_invitation_link_user_already_registered(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(
                '/invitations/register?token=544da5bd0f5fd72b880146fed9545cbe'
            );

            $browser->assertSee('Le lien d\'invitation a déjà été utilisé.')
                ->assertPathIs('/login');
        });
    }

    /**
     * Test invitation link with an invalid token.
     *
     * @throws Throwable
     */
    public function test_invitation_link_invalid_token(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(
                '/invitations/register?token=5c10872ae15b1f30d7db409bbf6983f4xxx'
            );

            $browser->assertSee('Mauvais jeton d\'invitation.')
                ->assertPathIs('/login');
        });
    }

    /**
     * Test invitation link with user creation.
     *
     * @throws Throwable
     */
    public function test_invitation_link(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(
                '/invitations/register?token=5c10872ae15b1f30d7db409bbf6983f4'
            );

            $browser->assertSee('Créer un nouvel utilisateur');

            $browser->type('name', 'Test invitation link')
                ->type('password', 'password')
                ->type('password_confirmation', 'password')
                ->press('Créer un nouvel utilisateur')
                ->waitForText('Compte créé. Votre lien d\'invitation ne peut plus être utilisé.')
                ->assertPathIs('/')
                ->assertSee('Compte créé. Votre lien d\'invitation ne peut plus être utilisé.');
        });
    }
}

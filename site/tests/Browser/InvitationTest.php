<?php

namespace Tests\Browser;

use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Throwable;

class InvitationTest extends DuskTestCase
{
    use ProvidesBrowser;

    public function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * Test list invitations.
     *
     * @return void
     * @throws Throwable
     */
    public function testListInvitations()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
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
     * @return void
     * @throws Throwable
     */
    public function testNotListRegisteredInvitations()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/invitations');
            $browser->assertDontSee('test-invitation-registered@example.com');
        });
    }

    /**
     * Test admin view all invitations.
     *
     * @return void
     * @throws Throwable
     */
    public function testAdminViewAllInvitations()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/invitations');
            $browser->assertSee('test-invitation@example.com');
            $browser->assertSee('test-invitation-user@example.com');
        });
    }

    /**
     * Test user view own invitations.
     *
     * @return void
     * @throws Throwable
     */
    public function testUserViewOwnInvitations()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('invitation-user@example.com', 'password');

            $browser->visit('/invitations');
            $browser->assertDontSee('test-invitation@example.com');
            $browser->assertSee('test-invitation-user@example.com');
        });
    }

    /**
     * Test create an invitation.
     *
     * @return void
     * @throws Throwable
     */
    public function testCreateInvitation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('invitation-user@example.com', 'password');

            $browser->visit('/invitations');

            $browser->clickLink('Créer une invitation');
            $browser->type('email', 'test-new-invitation@example.com')
                ->press('Créer une invitation')
                ->waitForText('Invitation créée. Un email à été envoyé au destinataire.')
                ->assertSee('Invitation créée. Un email à été envoyé au destinataire.');

            $browser->visit('/invitations');
            $browser->assertSee('test-new-invitation@example.com');
        });
    }

    /**
     * Test show the invitation link.
     *
     * @return void
     * @throws Throwable
     */
    public function testShowInvitationLink()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('invitation-user@example.com', 'password');

            $browser->visit('/invitations');
            $browser->click('#invitations table tbody tr:first-child .actions span:nth-child(1) button')
                ->waitForText('Lien de l\'invitation')
                ->assertSee('Lien de l\'invitation');
        });
    }

    /**
     * Test send invitation mail.
     *
     * @return void
     * @throws Throwable
     */
    public function testSendInvitationMail()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('invitation-user@example.com', 'password');

            $browser->visit('/invitations');
            $browser->click('#invitations table tbody tr:first-child .actions span:nth-child(2) a')
                ->waitForText('Mail d\'invitation envoyé à')
                ->assertSee('Mail d\'invitation envoyé à');
        });
    }

    /**
     * Test delete an invitation.
     *
     * @return void
     * @throws Throwable
     */
    public function testDeleteInvitation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('invitation-user@example.com', 'password');

            $browser->visit('/invitations');
            $browser->assertSee('test-invitation-user@example.com');

            $browser->click('#invitations form.with-delete-confirm button')
                ->waitForDialog($seconds = null)
                ->assertDialogOpened('Êtes-vous sûr de vouloir supprimer cet élément ?')
                ->acceptDialog()
                ->waitForText('Invitation supprimée.')
                ->assertSee('Invitation supprimée.');
        });
    }
}

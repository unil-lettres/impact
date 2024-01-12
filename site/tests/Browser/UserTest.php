<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\Browser\Pages\Profile;
use Tests\DuskTestCase;
use Throwable;

class UserTest extends DuskTestCase
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
     * Test list users.
     *
     * @throws Throwable
     */
    public function testListUsers(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users');

            $browser->assertSee('Gestion des utilisateurs');
            $browser->assertSee('first-user@example.com');
            $browser->assertSee('admin-user@example.com');
        });
    }

    /**
     * Test create user.
     *
     * @throws Throwable
     */
    public function testCreateUser(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users');

            $browser->assertSee('Créer un utilisateur')
                ->clickLink('Créer un utilisateur');

            $browser->type('name', 'Test create user')
                ->type('email', 'test-create-user@example.com')
                ->type('password', 'password')
                ->type('password_confirmation', 'password')
                ->press('Créer un nouvel utilisateur')
                ->waitForText('Compte utilisateur créé: test-create-user@example.com')
                ->assertSee('Compte utilisateur créé: test-create-user@example.com')
                ->assertSee('Test create user')
                ->assertPathIs('/admin/users');
        });
    }

    /**
     * Test create user with error.
     *
     * @throws Throwable
     */
    public function testCreateUserWithError(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users/create');

            $browser->type('name', 'Test create user with error')
                ->type('email', 'test-create-user-with-error@example.com')
                ->type('password', 'password1')
                ->type('password_confirmation', 'password2')
                ->press('Créer un nouvel utilisateur')
                ->waitForText('Le champ de confirmation password ne correspond pas.')
                ->assertSee('Le champ de confirmation password ne correspond pas.')
                ->assertPathIs('/admin/users/create');
        });
    }

    /**
     * Test edit user.
     *
     * @throws Throwable
     */
    public function testEditUser(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users');

            $browser->click('#users table tbody tr:first-child .actions span:nth-child(1) a')
                ->type('name', 'Test update user')
                ->type('email', 'test-update-user@example.com')
                ->type('old_password', 'password')
                ->type('new_password', 'password_updated')
                ->type('password_confirm', 'password_updated')
                ->press('Mettre à jour le compte')
                ->waitForText('Compte utilisateur mis à jour')
                ->assertSee('Compte utilisateur mis à jour')
                ->assertSee('Test update user')
                ->assertSee('test-update-user@example.com')
                ->assertPathIs('/admin/users');
        });
    }

    /**
     * Test edit user with errors.
     *
     * @throws Throwable
     */
    public function testEditUserWithErrors(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users');

            $browser->click('#users table tbody tr:first-child .actions span:nth-child(1) a')
                ->type('name', '')
                ->type('email', '')
                ->type('old_password', 'password')
                ->type('new_password', 'password1')
                ->type('password_confirm', 'password2')
                ->press('Mettre à jour le compte')
                ->waitForText('doivent être identiques.')
                ->assertSee('doivent être identiques.');

            $browser->type('name', 'Test update user with errors')
                ->type('email', 'test-update-user-with-errors@example.com')
                ->type('old_password', 'password-with-errors');

            $browser->scrollTo('@user-update-button') // Scroll to avoid "Element is not clickable at point" error
                ->press('Mettre à jour le compte')
                ->waitForText('Vous avez entré le mauvais mot de passe')
                ->assertSee('Vous avez entré le mauvais mot de passe');
        });
    }

    /**
     * Test expired user.
     *
     * @throws Throwable
     */
    public function testExpiredUser(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users');

            $browser->assertSee('Expiré');

            $browser->click('#users table tbody tr.invalid .actions span:nth-child(1) a')
                ->waitForText('Expiré')
                ->assertSee('Expiré')
                ->click('#edit-user .card .card-header a.extend-validity')
                ->waitForText('Prolongation de la validité du compte de l\'utilisateur')
                ->assertSee('Prolongation de la validité du compte de l\'utilisateur')
                ->assertDontSee('Expiré')
                ->assertPathIs('/admin/users');
        });
    }

    /**
     * Test AAI user.
     *
     * @throws Throwable
     */
    public function testAaiUser(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users');

            $browser->click('#users table tbody tr.aai .actions span:nth-child(1) a')
                ->waitForText('Nom')
                ->assertSee('Nom')
                ->assertSee('Email')
                ->assertSee('Type')
                ->assertDisabled('type')
                ->assertInputValue('type', 'aai')
                ->assertDontSee('Mot de passe actuel')
                ->assertDontSee('Nouveau mot de passe')
                ->assertDontSee('Confirmer le mot de passe');
        });
    }

    /**
     * Test local user.
     *
     * @throws Throwable
     */
    public function testLocalUser(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users');

            $browser->click('#users table tbody tr.local .actions span:nth-child(1) a')
                ->waitForText('Type')
                ->assertDisabled('type')
                ->assertInputValue('type', 'local')
                ->assertSee('Mot de passe actuel')
                ->assertSee('Nouveau mot de passe')
                ->assertSee('Confirmer le mot de passe');
        });
    }

    /**
     * Test delete user.
     *
     * @throws Throwable
     */
    public function testDeleteUser(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users');

            $browser->click('#users table tbody tr:nth-last-child(2) .actions form.with-delete-confirm button')
                ->waitForDialog($seconds = null)
                ->assertDialogOpened('Êtes-vous sûr de vouloir supprimer cet élément ?')
                ->acceptDialog()
                ->waitForText('Compte utilisateur supprimé')
                ->assertSee('Compte utilisateur supprimé')
                ->assertPathIs('/admin/users');
        });
    }

    /**
     * Test local user profile.
     *
     * @throws Throwable
     */
    public function testLocalUserProfile(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('first-user@example.com', 'password');

            $browser->visit(new Profile())
                ->profile();

            $browser->assertDisabled('name')
                ->assertDisabled('email')
                ->assertDisabled('type');

            $browser->assertSee('Nom')
                ->assertSee('Email')
                ->assertSee('Type')
                ->assertSee('Validité')
                ->assertSee('Mot de passe actuel')
                ->assertSee('Nouveau mot de passe')
                ->assertSee('Confirmer le mot de passe');
        });
    }

    /**
     * Test editing local user profile.
     *
     * @throws Throwable
     */
    public function testEditLocalUserProfile(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('first-user@example.com', 'password');

            $browser->visit(new Profile())
                ->profile();

            $browser->type('old_password', 'password')
                ->type('new_password', 'password1')
                ->type('password_confirm', 'password1')
                ->press('Mettre à jour le compte')
                ->waitForText('Compte utilisateur mis à jour')
                ->assertSee('Compte utilisateur mis à jour');
        });
    }
}

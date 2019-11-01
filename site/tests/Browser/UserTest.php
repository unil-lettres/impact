<?php

namespace Tests\Browser;

use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Throwable;

class UserTest extends DuskTestCase
{
    use ProvidesBrowser;

    public function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * Test list users.
     *
     * @return void
     * @throws Throwable
     */
    public function testListUsers()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Admin');

            $browser->assertSee('Géstion des utilisateurs');
            $browser->assertSee('first-user@example.com');
            $browser->assertSee('admin-user@example.com');
        });
    }

    /**
     * Test create user.
     *
     * @return void
     * @throws Throwable
     */
    public function testCreateUser()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Admin');

            $browser->assertSee('Créer un utilisateur');
            $browser->clickLink('Créer un utilisateur');

            $browser->type('name', 'Test create user')
                ->type('email', 'test-create-user@example.com')
                ->type('password', 'password')
                ->type('password_confirmation', 'password')
                ->press('Créer un nouvel utilisateur')
                ->waitForText('Compte utilisateur créé: test-create-user@example.com')
                ->assertSee('Compte utilisateur créé: test-create-user@example.com')
                ->assertSee('Test create user')
                ->assertUrlIs('/admin/users');
        });
    }

    /**
     * Test create user with error.
     *
     * @return void
     * @throws Throwable
     */
    public function testCreateUserWithError()
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
                ->assertUrlIs('/admin/users/create');
        });
    }

    /**
     * Test edit user.
     *
     * @return void
     * @throws Throwable
     */
    public function testEditUser()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Admin');

            $browser->click('#users table tbody tr:first-child .actions span:nth-child(1) a')
                ->type('name', 'Test update user')
                ->type('email', 'test-update-user@example.com')
                ->type('old_password', 'password')
                ->type('new_password', 'password_updated')
                ->type('password_confirm', 'password_updated')
                ->press('Mettre à jour le compte')
                ->waitForText('Compte utilisateur mis à jour')
                ->assertSee('Compte utilisateur mis à jour')
                ->assertSee('test-update-user@example.com')
                ->assertSee('Test update user')
                ->assertUrlIs('/admin/users');
        });
    }

    /**
     * Test edit user with errors.
     *
     * @return void
     * @throws Throwable
     */
    public function testEditUserWithErrors()
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
                ->waitForText('est obligatoire.')
                ->assertSee('est obligatoire.')
                ->assertSee('doivent être identiques.');

            $browser->type('name', 'Test update user with errors')
                ->type('email', 'test-update-user-with-errors@example.com')
                ->type('old_password', 'password-with-errors')
                ->press('Mettre à jour le compte')
                ->waitForText('Vous avez entré le mauvais mot de passe')
                ->assertSee('Vous avez entré le mauvais mot de passe');
        });
    }

    /**
     * Test expired user.
     *
     * @return void
     * @throws Throwable
     */
    public function testExpiredUser()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users');

            $browser->assertSee('Expiré');

            $browser->click('#users table tbody tr.invalid .actions span:nth-child(1) a')
                ->waitForText('Invalid user')
                ->assertSee('Invalid user')
                ->assertSee('Expiré')
                ->click('#edit-user card card-header a.extend-validity')
                ->waitForText('Prolongation de la validité du compte de l\'utilisateur: invalid-user@example.com')
                ->assertSee('Prolongation de la validité du compte de l\'utilisateur: invalid-user@example.com')
                ->assertDontSee('Expiré')
                ->assertUrlIs('/admin/users');
        });
    }

    /**
     * Test AAI user.
     *
     * @return void
     * @throws Throwable
     */
    public function testAaiUser()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users');

            $browser->click('#users table tbody tr.aai .actions span:nth-child(1) a')
                ->waitForText('aai')
                ->assertSee('aai')
                ->assertDontSee('Mot de passe actuel')
                ->assertDontSee('Nouveau mot de passe')
                ->assertDontSee('Confirmer le mot de passe');
        });
    }

    /**
     * Test local user.
     *
     * @return void
     * @throws Throwable
     */
    public function testLocalUser()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users');

            $browser->click('#users table tbody tr.local .actions span:nth-child(1) a')
                ->waitForText('local')
                ->assertSee('local')
                ->assertSee('Mot de passe actuel')
                ->assertSee('Nouveau mot de passe')
                ->assertSee('Confirmer le mot de passe');
        });
    }

    /**
     * Test delete user.
     *
     * @return void
     * @throws Throwable
     */
    public function testDeleteUser()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users');

            $browser->click('#users table tbody tr:first-child .actions form.with-delete-confirm button')
                ->waitForDialog($seconds = null)
                ->assertDialogOpened('Êtes-vous sûr de vouloir supprimer cet élément ?')
                ->acceptDialog()
                ->waitForText('Compte utilisateur supprimé')
                ->assertSee('Compte utilisateur supprimé')
                ->assertUrlIs('/admin/users');
        });
    }
}

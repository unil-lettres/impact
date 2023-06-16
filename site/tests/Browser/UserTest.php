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
     * Test list users.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testListUsers()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Admin');

            $browser->assertSee('Gestion des utilisateurs');
            $browser->assertSee('first-user@example.com');
            $browser->assertSee('admin-user@example.com');
        });
    }

    /**
     * Test create user.
     *
     * @return void
     *
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
                ->assertPathIs('/admin/users');
        });
    }

    /**
     * Test create user with error.
     *
     * @return void
     *
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
                ->assertPathIs('/admin/users/create');
        });
    }

    /**
     * Test edit user.
     *
     * @return void
     *
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
                ->assertSee('Test update user')
                ->assertInputValue('name', 'Test update user')
                ->assertInputValue('email', 'test-update-user@example.com');
        });
    }

    /**
     * Test edit user with errors.
     *
     * @return void
     *
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
     * @return void
     *
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
     * @return void
     *
     * @throws Throwable
     */
    public function testAaiUser()
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
                ->assertInputValue('type', 'aai')
                ->assertDontSee('Mot de passe actuel')
                ->assertDontSee('Nouveau mot de passe')
                ->assertDontSee('Confirmer le mot de passe');
        });
    }

    /**
     * Test local user.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testLocalUser()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users');

            $browser->click('#users table tbody tr.local .actions span:nth-child(1) a')
                ->waitForText('Type')
                ->assertInputValue('type', 'local')
                ->assertSee('Mot de passe actuel')
                ->assertSee('Nouveau mot de passe')
                ->assertSee('Confirmer le mot de passe');
        });
    }

    /**
     * Test delete user.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testDeleteUser()
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
     * @return void
     *
     * @throws Throwable
     */
    public function testLocalUserProfile()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('first-user@example.com', 'password');

            $browser->visit(new Profile())
                ->profile();

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
     * @return void
     *
     * @throws Throwable
     */
    public function testEditLocalUserProfile()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('first-user@example.com', 'password');

            $browser->visit(new Profile())
                ->profile();

            $browser->type('name', 'First user updated')
                ->press('Mettre à jour le compte')
                ->waitForText('Compte utilisateur mis à jour')
                ->assertSee('Compte utilisateur mis à jour')
                ->assertSee('First user updated');
        });
    }
}

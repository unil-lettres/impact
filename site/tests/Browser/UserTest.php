<?php

namespace Tests\Browser;

use App\Scopes\ValidityScope;
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
     * Test list users.
     *
     * @throws Throwable
     */
    public function test_list_users(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users');

            $browser->waitFor('#users table tbody')
                ->assertSee('Gestion des utilisateurs');
            $browser->assertSee('first-user@example.com');
            $browser->assertSee('admin-user@example.com');
        });
    }

    /**
     * Test create user.
     *
     * @throws Throwable
     */
    public function test_create_user(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users/create');

            $browser->waitForText('Créer un nouvel utilisateur local')
                ->waitFor('#name')
                ->type('name', 'Test create user')
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
    public function test_create_user_with_error(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users/create');

            $browser->waitForText('Créer un nouvel utilisateur local')
                ->type('name', 'Test create user with error')
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
    public function test_edit_user(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $userId = \App\User::where('email', 'first-user@example.com')
                ->value('id');
            $browser->visit("/admin/users/{$userId}/edit")
                ->waitForText('First user')
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
    public function test_edit_user_with_errors(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $userId = \App\User::where('email', 'first-user@example.com')
                ->value('id');
            $browser->visit("/admin/users/{$userId}/edit")
                ->waitForText('First user')
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
    public function test_expired_user(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $userId = \App\User::where('email', 'invalid-user@example.com')
                ->withoutGlobalScope(ValidityScope::class)
                ->value('id');
            $browser->visit("/admin/users/{$userId}/edit")
                ->waitForText('Invalid user')
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
    public function test_aai_user(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $userId = \App\User::where('email', 'aai-user@example.com')
                ->value('id');
            $browser->visit("/admin/users/{$userId}/edit")
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
    public function test_local_user(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $userId = \App\User::where('email', 'first-user@example.com')
                ->value('id');
            $browser->visit("/admin/users/{$userId}/edit")
                ->waitForText('First user')
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
    public function test_delete_user(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users');

            $userId = \App\User::where('email', 'first-user@example.com')
                ->value('id');
            $browser->waitFor('#users table tbody');
            $this->stubConfirmAndClick(
                $browser,
                "form[action$='/admin/users/{$userId}'].with-delete-confirm button"
            );
            $browser->waitForText('Compte utilisateur supprimé')
                ->assertSee('Compte utilisateur supprimé')
                ->assertPathIs('/admin/users');
        });
    }

    /**
     * Test local user profile.
     *
     * @throws Throwable
     */
    public function test_local_user_profile(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('first-user@example.com', 'password');

            $browser->visit(new Profile)
                ->profile();

            $browser->waitFor('input[name="name"]')
                ->assertDisabled('name')
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
    public function test_edit_local_user_profile(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('first-user@example.com', 'password');

            $browser->visit(new Profile)
                ->profile();

            $browser->waitFor('input[name="old_password"]')
                ->type('old_password', 'password')
                ->type('new_password', 'password1')
                ->type('password_confirm', 'password1')
                ->press('Mettre à jour le compte')
                ->waitForText('Compte utilisateur mis à jour')
                ->assertSee('Compte utilisateur mis à jour');
        });
    }
}

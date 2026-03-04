<?php

namespace Tests\Browser;

use App\Scopes\ValidityScope;
use App\User;
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

            $browser->visit('/admin/users')
                ->waitForText('Gestion des utilisateurs');

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
    public function test_create_user(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users')
                ->waitForText('Créer un utilisateur');

            $browser->assertSee('Créer un utilisateur')
                ->clickLink('Créer un utilisateur');

            $browser->waitFor('[name="name"]')
                ->type('name', 'Test create user')
                ->type('email', 'test-create-user@example.com')
                ->type('password', 'password')
                ->type('password_confirmation', 'password')
                ->scrollTo('button[type="submit"]')
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

            $browser->visit('/admin/users/create')
                ->waitFor('[name="name"]');

            $browser->type('name', 'Test create user with error')
                ->type('email', 'test-create-user-with-error@example.com')
                ->type('password', 'password1')
                ->type('password_confirmation', 'password2')
                ->scrollTo('button[type="submit"]')
                ->press('Créer un nouvel utilisateur')
                ->waitFor('.alert-danger')
                ->assertPresent('.alert-danger')
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
            $user = User::where('email', 'first-user@example.com')->first();

            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit("/admin/users/{$user->id}/edit")
                ->waitFor('[name="name"]')
                ->type('name', 'Test update user')
                ->type('email', 'test-update-user@example.com')
                ->type('old_password', 'password')
                ->type('new_password', 'password_updated')
                ->type('password_confirm', 'password_updated')
                ->scrollTo('@user-update-button')
                ->press('Mettre à jour le compte')
                ->waitForLocation('/admin/users')
                ->assertPathIs('/admin/users');

            $updatedUser = User::where('id', $user->id)->first();
            $this->assertSame('Test update user', $updatedUser->name);
            $this->assertSame('test-update-user@example.com', $updatedUser->email);
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
            $user = User::where('email', 'first-user@example.com')->first();

            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit("/admin/users/{$user->id}/edit")
                ->waitFor('[name="name"]')
                ->type('name', '')
                ->type('email', '')
                ->type('old_password', 'password')
                ->type('new_password', 'password1')
                ->type('password_confirm', 'password2')
                ->press('Mettre à jour le compte')
                ->waitFor('.alert-danger')
                ->assertPresent('.alert-danger');

            $browser->type('name', 'Test update user with errors')
                ->type('email', 'test-update-user-with-errors@example.com')
                ->type('old_password', 'password-with-errors')
                ->scrollTo('@user-update-button')
                ->press('Mettre à jour le compte')
                ->waitFor('.alert-danger')
                ->assertPresent('.alert-danger')
                ->assertPathIs("/admin/users/{$user->id}/edit");

            $freshUser = User::where('id', $user->id)->first();
            $this->assertSame('first-user@example.com', $freshUser->email);
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
            $user = User::withoutGlobalScope(ValidityScope::class)
                ->where('email', 'invalid-user@example.com')->first();

            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users')
                ->waitFor("[dusk='user-edit-{$user->id}']")
                ->assertSee('Expiré');

            $browser->visit("/admin/users/{$user->id}/edit")
                ->waitFor('[name="name"]')
                ->waitFor('#edit-user .card .card-header a.extend-validity')
                ->click('#edit-user .card .card-header a.extend-validity')
                ->waitForLocation('/admin/users')
                ->assertPathIs('/admin/users');

            $refreshedUser = User::withoutGlobalScope(ValidityScope::class)
                ->where('id', $user->id)->first();
            $this->assertTrue($refreshedUser->isValid());
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
            $user = User::where('email', 'aai-user@example.com')->first();

            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit("/admin/users/{$user->id}/edit")
                ->waitFor('[name="type"]')
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
            $user = User::where('email', 'first-user@example.com')->first();

            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit("/admin/users/{$user->id}/edit")
                ->waitFor('[name="type"]')
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
            $user = User::where('email', 'first-user@example.com')->first();

            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/users')
                ->waitFor("[dusk='user-delete-{$user->id}']")
                ->click("[dusk='user-delete-{$user->id}']")
                ->waitForLocation('/admin/users')
                ->assertPathIs('/admin/users');

            $this->assertTrue(User::where('id', $user->id)->doesntExist());
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
    public function test_edit_local_user_profile(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('first-user@example.com', 'password');

            $browser->visit(new Profile)
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

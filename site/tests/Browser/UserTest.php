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
     * Test AAI user.
     *
     * @throws Throwable
     */
    public function test_aai_user(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $userId = \App\User::where('email', 'aai-user@example.com')->value('id');
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

            $userId = \App\User::where('email', 'first-user@example.com')->value('id');
            $browser->waitFor("#users table tbody");
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

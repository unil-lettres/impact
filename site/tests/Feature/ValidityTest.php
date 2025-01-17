<?php

namespace Tests\Feature;

use App\Mail\AccountValidity;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test invalid user.
     */
    public function test_invalid_user(): void
    {
        $user = User::factory()
            ->invalid()
            ->create();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect('/login');
    }

    /**
     * Test extend user account validity.
     */
    public function test_extend_account_validity(): void
    {
        $user = User::factory()
            ->invalid()
            ->create();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect('/login');

        $user->extendValidity();

        $this->actingAs($user)
            ->get('/')
            ->assertOk();
    }

    /**
     * Test validity not applied to admins.
     */
    public function test_no_validity_for_admins(): void
    {
        $user = User::factory()
            ->admin()
            ->invalid()
            ->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk();
    }

    /**
     * Test the command sending an email if a user account is about to expire.
     */
    public function test_command_email_account_validity(): void
    {
        $this->artisan('email:account:validity')
            ->assertExitCode(0);
    }

    /**
     * Test the user account is about to expire email content.
     */
    public function test_account_validity_email_content(): void
    {
        $days = config('const.users.account.expiring');

        $user = User::factory()
            ->expireIn($days)
            ->create();

        $mailable = new AccountValidity($user, $days);

        $mailable->assertSeeInHtml(route('home'));
        $mailable->assertSeeInHtml($days);
    }

    /**
     * Test the user account is expiring in the number of days defined in the configuration.
     */
    public function test_account_expiring_check(): void
    {
        $days = config('const.users.account.expiring');

        $user = User::factory()
            ->expireIn($days)
            ->create();
        // Account is expiring in the number of days defined in the configuration
        $this->assertTrue($user->isAccountExpiringIn($days));

        $user = User::factory()
            ->expireIn($days + 1)
            ->create();
        // Account is not expiring in the number of days defined in the configuration
        $this->assertFalse($user->isAccountExpiringIn($days));
    }
}

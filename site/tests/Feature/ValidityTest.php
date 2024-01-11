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
    public function testInvalidUser(): void
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
    public function testExtendAccountValidity(): void
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
    public function testNoValidityForAdmins(): void
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
    public function testCommandEmailAccountValidity(): void
    {
        $this->artisan('email:account:validity')
            ->assertExitCode(0);
    }

    /**
     * Test the user account is about to expire email content.
     */
    public function testAccountValidityEmailContent(): void
    {
        $days = config('const.users.account.expiring');

        $user = User::factory()
            ->expireIn($days)
            ->create();

        $mailable = new AccountValidity($user, $days);

        $mailable->assertSeeInHtml(route('home'));
        $mailable->assertSeeInHtml($days);
    }
}

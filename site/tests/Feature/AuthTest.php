<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test basic auth.
     */
    public function test_basic_auth(): void
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk();
    }

    /**
     * Test admin not authorized.
     */
    public function test_admin_not_authorized(): void
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect('/');
    }

    /**
     * Test admin authorized.
     */
    public function test_admin_authorized(): void
    {
        $user = User::factory()
            ->admin()
            ->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect('/admin/users');
    }
}

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
    public function testBasicAuth(): void
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
    public function testAdminNotAuthorized(): void
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
    public function testAdminAuthorized(): void
    {
        $user = User::factory()
            ->admin()
            ->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect('/admin/users');
    }
}

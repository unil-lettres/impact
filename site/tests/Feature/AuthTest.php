<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * Test basic auth.
     *
     * @return void
     */
    public function testBasicAuth()
    {
        $user = User::factory()
            ->make();

        $this->actingAs($user)
            ->get('/')
            ->assertOk();
    }

    /**
     * Test admin not authorized.
     *
     * @return void
     */
    public function testAdminNotAuthorized()
    {
        $user = User::factory()
            ->make();

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect('/');
    }

    /**
     * Test admin authorized.
     *
     * @return void
     */
    public function testAdminAuthorized()
    {
        $user = User::factory()
            ->admin()
            ->make();

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect('/admin/users');
    }
}

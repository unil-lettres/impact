<?php

namespace Tests\Feature;

use App\User;
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
            ->create();

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
            ->create();

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
            ->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect('/admin/users');
    }
}

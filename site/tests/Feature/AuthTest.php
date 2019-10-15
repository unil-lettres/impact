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
        $user = factory(User::class)
            ->create();

        $response = $this->actingAs($user)
            ->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test admin not authorized.
     *
     * @return void
     */
    public function testAdminNotAuthorized()
    {
        $user = factory(User::class)
            ->create();

        $response = $this->actingAs($user)
            ->get('/admin');

        $response->assertStatus(403);
    }

    /**
     * Test admin authorized.
     *
     * @return void
     */
    public function testAdminAuthorized()
    {
        $user = factory(User::class)
            ->states('admin')
            ->create();

        $response = $this->actingAs($user)
            ->get('/admin');

        $response->assertStatus(200);
    }
}

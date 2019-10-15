<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
        $user = factory(User::class)
            ->create();

        try {
            $this->actingAs($user)->get('/admin');
        } catch (\Throwable $e) {
            $this->assertEquals(new HttpException(403), $e);
        }
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

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }
}

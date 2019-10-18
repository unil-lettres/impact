<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidityTest extends TestCase
{
    /**
     * Test invalid user.
     *
     * @return void
     */
    public function testInvalidUser()
    {
        $user = factory(User::class)
            ->states('invalid')
            ->create();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect('/login');
    }

    /**
     * Test extend user account validity.
     *
     * @return void
     */
    public function testExtendAccountValidity()
    {
        $user = factory(User::class)
            ->states('invalid')
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
     *
     * @return void
     */
    public function testNoValidityForAdmins()
    {
        $user = factory(User::class)
            ->states('invalid')
            ->states('admin')
            ->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk();
    }
}

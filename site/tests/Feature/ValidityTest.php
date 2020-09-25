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
        $user = User::factory()
            ->invalid()
            ->make();

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
        $user = User::factory()
            ->invalid()
            ->make();

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
        $user = User::factory()
            ->invalid()
            ->make();

        $this->actingAs($user)
            ->get('/')
            ->assertOk();
    }
}

<?php

namespace Tests\Feature;

use App\Course;
use App\Enrollment;
use App\Enums\EnrollmentRole;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidityScopeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that valid users are visible to regular users.
     */
    public function test_valid_users_are_visible(): void
    {
        $regularUser = User::factory()->create();
        $validUser = User::factory()->create();

        $this->actingAs($regularUser);

        $users = User::all();

        $this->assertTrue($users->contains('id', $validUser->id));
    }

    /**
     * Test that invalid users are hidden from regular users.
     */
    public function test_invalid_users_are_hidden_from_regular_users(): void
    {
        $regularUser = User::factory()->create();
        $invalidUser = User::factory()->invalid()->create();

        $this->actingAs($regularUser);

        $users = User::all();

        $this->assertFalse($users->contains('id', $invalidUser->id));
    }

    /**
     * Test that invalid users are visible to admin users.
     */
    public function test_invalid_users_are_visible_to_admins(): void
    {
        $adminUser = User::factory()->admin()->create();
        $invalidUser = User::factory()->invalid()->create();

        $this->actingAs($adminUser);

        $users = User::all();

        $this->assertTrue($users->contains('id', $invalidUser->id));
    }

    /**
     * Test that users without validity date are visible.
     */
    public function test_users_without_validity_are_visible(): void
    {
        $regularUser = User::factory()->create();
        $userWithoutValidity = User::factory()->create(['validity' => null]);

        $this->actingAs($regularUser);

        $users = User::all();

        $this->assertTrue($users->contains('id', $userWithoutValidity->id));
    }

    /**
     * Test that enrollments with invalid users are hidden from regular users.
     */
    public function test_enrollments_with_invalid_users_are_hidden(): void
    {
        $regularUser = User::factory()->create();
        $invalidUser = User::factory()->invalid()->create();
        $course = Course::factory()->create();

        // Create enrollment for invalid user (bypass global scope)
        $enrollment = new Enrollment([
            'user_id' => $invalidUser->id,
            'course_id' => $course->id,
            'role' => EnrollmentRole::Member,
        ]);
        $enrollment->saveQuietly();

        $this->actingAs($regularUser);

        $enrollments = Enrollment::all();

        $this->assertFalse($enrollments->contains('id', $enrollment->id));
    }

    /**
     * Test that enrollments with invalid users are visible to admins.
     */
    public function test_enrollments_with_invalid_users_are_visible_to_admins(): void
    {
        $adminUser = User::factory()->admin()->create();
        $invalidUser = User::factory()->invalid()->create();
        $course = Course::factory()->create();

        // Create enrollment for invalid user (bypass global scope)
        $enrollment = new Enrollment([
            'user_id' => $invalidUser->id,
            'course_id' => $course->id,
            'role' => EnrollmentRole::Member,
        ]);
        $enrollment->saveQuietly();

        $this->actingAs($adminUser);

        $enrollments = Enrollment::all();

        $this->assertTrue($enrollments->contains('id', $enrollment->id));
    }

    /**
     * Test that enrollments with valid users are visible.
     */
    public function test_enrollments_with_valid_users_are_visible(): void
    {
        $regularUser = User::factory()->create();
        $validUser = User::factory()->create();
        $course = Course::factory()->create();

        $enrollment = new Enrollment([
            'user_id' => $validUser->id,
            'course_id' => $course->id,
            'role' => EnrollmentRole::Member,
        ]);
        $enrollment->saveQuietly();

        $this->actingAs($regularUser);

        $enrollments = Enrollment::all();

        $this->assertTrue($enrollments->contains('id', $enrollment->id));
    }

    /**
     * Test that enrollments from deleted courses are hidden.
     */
    public function test_enrollments_from_deleted_courses_are_hidden(): void
    {
        $regularUser = User::factory()->create();
        $validUser = User::factory()->create();
        $course = Course::factory()->disabled()->create();

        $enrollment = new Enrollment([
            'user_id' => $validUser->id,
            'course_id' => $course->id,
            'role' => EnrollmentRole::Member,
        ]);
        $enrollment->saveQuietly();

        $this->actingAs($regularUser);

        $enrollments = Enrollment::all();

        $this->assertFalse($enrollments->contains('id', $enrollment->id));
    }
}

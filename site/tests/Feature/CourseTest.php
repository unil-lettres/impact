<?php

namespace Tests\Feature;

use App\Course;
use App\Mail\CourseConfirmDelete;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_course_can_be_created(): void
    {
        $courseData = [
            'name' => fake()->sentence(),
            'description' => fake()->text(),
        ];

        $course = Course::factory()->create($courseData);

        $this->assertInstanceOf(Course::class, $course);
        $this->assertDatabaseHas('courses', $courseData);
    }

    public function test_course_can_be_updated(): void
    {
        $course = Course::factory()->create();

        $courseDataUpdated = [
            'name' => 'Updated Test Course Name',
            'description' => 'Updated Test Course Description',
        ];

        $course->update($courseDataUpdated);

        $this->assertDatabaseHas('courses', $courseDataUpdated);
    }

    public function test_course_can_be_deleted(): void
    {
        $course = Course::factory()->create();

        $course->delete();

        $this->assertSoftDeleted($course);
    }

    /**
     * Test the course confirm delete email content.
     */
    public function test_course_confirm_delete_email_content(): void
    {
        $course = Course::factory()
            ->disabled()
            ->create();

        $mailable = new CourseConfirmDelete($course);

        $mailable->assertSeeInHtml($course->name);
    }
}

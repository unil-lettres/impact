<?php

namespace Tests\Feature;

use App\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testCourseCanBeCreated()
    {
        $courseData = [
            'name' => fake()->sentence(),
            'description' => fake()->text(),
        ];

        $course = Course::factory()->create($courseData);

        $this->assertInstanceOf(Course::class, $course);
        $this->assertDatabaseHas('courses', $courseData);
    }

    public function testCourseCanBeUpdated()
    {
        $course = Course::factory()->create();

        $courseDataUpdated = [
            'name' => 'Updated Test Course Name',
            'description' => 'Updated Test Course Description',
        ];

        $course->update($courseDataUpdated);

        $this->assertDatabaseHas('courses', $courseDataUpdated);
    }

    public function testCourseCanBeDeleted()
    {
        $course = Course::factory()->create();

        $course->delete();

        $this->assertSoftDeleted($course);
    }
}

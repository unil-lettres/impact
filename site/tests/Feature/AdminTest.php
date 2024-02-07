<?php

namespace Tests\Feature;

use App\Course;
use App\Mail\ManagersMailing;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Test the managers mailing email content.
     */
    public function testManagersMailingEmailContent(): void
    {
        $user = User::factory()->create();
        $courses = collect([
            Course::factory()->create(),
            Course::factory()->create(),
        ]);

        $subject = $this->faker->sentence(3);
        $content = '{{espaces}}';

        $mailable = new ManagersMailing(
            $user,
            $subject,
            $content,
            $courses
        );

        $courses->each(function ($course) use ($mailable) {
            $mailable->assertSeeInHtml($course->name);
            $mailable->assertSeeInHtml(route('courses.show', $course->id));
        });
    }
}

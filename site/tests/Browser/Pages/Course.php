<?php

namespace Tests\Browser\Pages;

use App\Card;
use App\Course as AppCourse;
use Laravel\Dusk\Browser;

class Course extends Page
{
    private AppCourse $course;

    public function __construct(string $courseName)
    {
        $this->course = AppCourse::where('name', $courseName)->first();
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return "/courses/{$this->course->id}";
    }

    /**
     * Return the id of the course.
     */
    public function id(): int
    {
        return $this->course->id;
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@element' => '#selector',
            '@multi-menu' => '.toolsbox .dropdown',
        ];
    }

    /**
     * Wait for the finder to be fully loaded.
     */
    public function waitUntilLoaded(Browser $browser): void
    {
        $browser->waitForText('Tout ouvrir');
    }

    /**
     * Create a card for this course with the given title.
     */
    public function createCard(Browser $browser, string $title): void
    {
        Card::factory()->create([
            'title' => $title,
            'course_id' => $this->course->id,
        ]);
    }
}

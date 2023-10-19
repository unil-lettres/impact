<?php

namespace Tests\Browser\Pages;

use App\Card;
use App\Course as AppCourse;
use Laravel\Dusk\Browser;

class Course extends Page
{
    private AppCourse $course;

    public function __construct(string $courseName){
        $this->course = AppCourse::where('name', $courseName)->first();
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return "/courses/{$this->course->id}";
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@element' => '#selector',
            '@multi-menu' => '.toolsbox .dropdown',
        ];
    }

    public function waitUntilLoaded(Browser $browser)
    {
        $browser->waitForText('Tout ouvrir');
    }

    public function createCard(Browser $browser, string $title) {
        Card::factory()->create([
            'title' => $title,
            'course_id' => $this->course->id,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'course_id' => Course::factory(),
        ];
    }

    /**
     * Indicate that the transcription version is the one specified.
     */
    public function transcriptionVersion($version): Factory
    {
        return $this->state(
            fn (array $attributes) => ['box2->version' => $version],
        );
    }
}

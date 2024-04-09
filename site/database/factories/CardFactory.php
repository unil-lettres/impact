<?php

namespace Database\Factories;

use App\Card;
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
}

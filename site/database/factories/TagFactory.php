<?php

namespace Database\Factories;

use App\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            // Using uuid() to avoid occasional collisions word() would cause
            // because of unique (course_id, name) constraint.
            'name' => fake()->uuid(),
        ];
    }
}

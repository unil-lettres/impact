<?php

namespace Database\Factories;

use App\Course;
use App\State;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StateFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $now = Carbon::now();

        return [
            'name' => fake()->word(),
            'position' => fake()->randomDigitNotNull(),
            'course_id' => Course::factory(),
            'permissions' => json_decode(State::PERMISSIONS, true),
            'actions' => json_decode(State::ACTIONS, true),
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Course;
use App\State;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = State::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $now = Carbon::now();

        return [
            'name' => fake()->word(),
            'position' => fake()->randomDigitNotNull(),
            'course_id' => Course::factory(),
            'permissions' => json_decode(State::PERMISSIONS),
            'actions' => json_decode(State::ACTIONS),
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Course;
use App\Enums\StateType;
use App\State;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'name' => fake()->word(),
            'position' => fake()->randomDigitNotNull(),
            'course_id' => Course::factory(),
            'permissions' => json_decode(State::PERMISSIONS),
            'actions' => json_decode(State::ACTIONS),
        ];
    }

    /**
     * Indicate that the state has a private type.
     *
     * @return Factory
     */
    public function private()
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => 0,
                'type' => StateType::Private,
            ];
        });
    }

    /**
     * Indicate that the state has an archived type.
     *
     * @return Factory
     */
    public function archived()
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => 1000,
                'type' => StateType::Archived,
            ];
        });
    }
}

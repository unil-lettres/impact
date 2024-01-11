<?php

namespace Database\Factories;

use App\Course;
use App\Enums\CourseType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(),
            'description' => fake()->text(),
            'type' => CourseType::Local,
        ];
    }

    /**
     * Indicate that the course is disabled.
     */
    public function disabled(): Factory
    {
        $now = Carbon::now();

        return $this->state(function (array $attributes) use ($now) {
            return [
                'deleted_at' => $now,
            ];
        });
    }
}

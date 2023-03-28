<?php

namespace Database\Factories;

use App\Course;
use App\Enums\CourseType;
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
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => fake()->sentence(),
            'description' => fake()->text(),
            'type' => CourseType::Local,
        ];
    }
}

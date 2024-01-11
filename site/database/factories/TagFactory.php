<?php

namespace Database\Factories;

use App\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tag::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            // Using uuid() to avoid occasional collisions word() would cause
            // because of unicity (course_id, name) constraint.
            'name' => fake()->uuid(),
        ];
    }
}

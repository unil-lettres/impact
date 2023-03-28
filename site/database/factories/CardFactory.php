<?php

namespace Database\Factories;

use App\Card;
use App\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Card::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => fake()->sentence(),
            'box2' => json_decode(Card::TRANSCRIPTION),
            'course_id' => Course::factory(),
            'options' => json_decode(Card::OPTIONS),
        ];
    }
}

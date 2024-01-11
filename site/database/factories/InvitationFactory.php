<?php

namespace Database\Factories;

use App\Course;
use App\Invitation;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvitationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invitation::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $now = Carbon::now();
        $email = fake()->unique()->safeEmail;

        return [
            'email' => $email,
            'invitation_token' => substr(md5(rand(0, 9).$email.time()), 0, 32),
            'creator_id' => User::factory(),
            'course_id' => Course::factory(),
            'registered_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Course;
use App\Enums\InvitationType;
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

    private string $email;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $now = Carbon::now();
        $this->email = fake()->unique()->safeEmail;

        return [
            'email' => $this->email,
            'creator_id' => User::factory(),
            'course_id' => Course::factory(),
            'registered_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * Indicate that the invitation has the aai type.
     */
    public function aai(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'invitation_token' => null,
                'type' => InvitationType::Aai,
            ];
        });
    }

    /**
     * Indicate that the invitation has the local type.
     */
    public function local(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'invitation_token' => substr(md5(rand(0, 9).$this->email.time()), 0, 32),
                'type' => InvitationType::Local,
            ];
        });
    }
}

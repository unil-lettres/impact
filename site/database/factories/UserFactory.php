<?php

namespace Database\Factories;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => fake()->name,
            'email' => fake()->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is an admin.
     *
     * @return Factory
     */
    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'admin' => true,
            ];
        });
    }

    /**
     * Indicate that the user has an invalid account.
     *
     * @return Factory
     */
    public function invalid()
    {
        $now = Carbon::now();

        return $this->state(function (array $attributes) use ($now) {
            return [
                'validity' => $now->subDays(1),
            ];
        });
    }
}

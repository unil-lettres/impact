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
     */
    public function definition(): array
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
     */
    public function admin(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'admin' => true,
            ];
        });
    }

    /**
     * Indicate that the user has an invalid account.
     */
    public function invalid(): Factory
    {
        $now = Carbon::now();

        return $this->state(function (array $attributes) use ($now) {
            return [
                'validity' => $now->subDays(1),
            ];
        });
    }

    /**
     * Indicate that the user account is expiring in a specified number of days.
     */
    public function expireIn(?int $days): Factory
    {
        $now = Carbon::now();

        $days = $days ?? config('const.users.account.expiring');

        return $this->state(function (array $attributes) use ($now, $days) {
            return [
                'validity' => $now->addDays($days),
            ];
        });
    }
}

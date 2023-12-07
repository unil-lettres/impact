<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $admin = User::create([
            'name' => 'Admin user',
            'email' => 'admin-user@example.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
            'admin' => true,
        ])->id;

        User::create([
            'name' => 'First user',
            'email' => 'first-user@example.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
            'creator_id' => $admin,
            'validity' => Carbon::now()->addMonths(config('const.users.validity')),
        ]);

        User::create([
            'name' => 'Invalid user',
            'email' => 'invalid-user@example.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
            'creator_id' => $admin,
            'validity' => Carbon::now()->subDays(1),
        ]);

        User::create([
            'name' => 'AAI user',
            'email' => 'aai-user@example.com',
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
            'type' => UserType::Aai,
        ]);
    }
}

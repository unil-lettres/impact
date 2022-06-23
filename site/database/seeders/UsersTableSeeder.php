<?php

namespace Database\Seeders;

use App\Enums\UserType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

        $admin = DB::table('users')->insertGetId([
            'name' => 'Admin user',
            'email' => 'admin-user@example.com',
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
            'admin' => true,
        ]);

        DB::table('users')->insert([
            'name' => 'First user',
            'email' => 'first-user@example.com',
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
            'creator_id' => $admin,
            'validity' => Carbon::now()->addMonths(config('const.users.validity')),
        ]);

        DB::table('users')->insert([
            'name' => 'Invalid user',
            'email' => 'invalid-user@example.com',
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
            'creator_id' => $admin,
            'validity' => Carbon::now()->subDays(1),
        ]);

        DB::table('users')->insert([
            'name' => 'AAI user',
            'email' => 'aai-user@example.com',
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
            'type' => UserType::Aai,
        ]);
    }
}

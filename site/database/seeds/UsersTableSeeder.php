<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon\Carbon::now();

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
            'creator_id' => $admin
        ]);

        DB::table('users')->insert([
            'name' => 'Disabled user',
            'email' => 'disabled-user@example.com',
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
            'disabled' => true,
            'creator_id' => $admin
        ]);
    }
}

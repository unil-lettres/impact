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

        DB::table('users')->insert([
          'name' => 'First user',
          'email' => 'first-user@example.com',
          'password' => bcrypt('password'),
          'remember_token' => Str::random(10),
          'created_at' => $now,
          'updated_at' => $now,
        ]);

        DB::table('users')->insert([
            'name' => 'Admin user',
            'email' => 'admin-user@example.com',
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
            'admin' => true,
        ]);
    }
}

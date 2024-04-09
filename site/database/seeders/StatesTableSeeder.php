<?php

namespace Database\Seeders;

use App\Course;
use App\Enrollment;
use App\Enums\EnrollmentRole;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $now = Carbon::now();

        $course = Course::create([
            'name' => 'Test states',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ])->id;

        $managerUser = User::create([
            'name' => 'States manager user',
            'email' => 'states-manager-user@example.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ])->id;

        $memberUser = User::create([
            'name' => 'States member user',
            'email' => 'states-member-user@example.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ])->id;

        Enrollment::create([
            'role' => EnrollmentRole::Manager,
            'course_id' => $course,
            'user_id' => $managerUser,
        ]);

        Enrollment::create([
            'role' => EnrollmentRole::Member,
            'course_id' => $course,
            'user_id' => $memberUser,
        ]);
    }
}

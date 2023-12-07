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
    public function run()
    {
        $now = Carbon::now();

        $course = Course::create([
            'name' => 'Test states',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ])->id;

        $teacherUser = User::create([
            'name' => 'States teacher user',
            'email' => 'states-teacher-user@example.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ])->id;

        $studentUser = User::create([
            'name' => 'States student user',
            'email' => 'states-student-user@example.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ])->id;

        Enrollment::create([
            'role' => EnrollmentRole::Teacher,
            'course_id' => $course,
            'user_id' => $teacherUser,
        ]);

        Enrollment::create([
            'role' => EnrollmentRole::Student,
            'course_id' => $course,
            'user_id' => $studentUser,
        ]);
    }
}

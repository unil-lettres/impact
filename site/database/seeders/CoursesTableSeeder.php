<?php

namespace Database\Seeders;

use App\Course;
use App\Enrollment;
use App\Enums\CourseType;
use App\Enums\EnrollmentRole;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CoursesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $firstCourse = Course::create([
            'name' => 'First space',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ])->id;

        $secondCourse = Course::create([
            'name' => 'Second space',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ])->id;

        $deactivatedCourse = Course::create([
            'name' => 'Deactivated space',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => $now,
        ])->id;

        $externalCourse = Course::create([
            'name' => 'External space',
            'type' => CourseType::External,
            'external_id' => 12345678,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ])->id;

        $teacherUser = User::create([
            'name' => 'Teacher user',
            'email' => 'teacher-user@example.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ])->id;

        $studentUser = User::create([
            'name' => 'Student user',
            'email' => 'student-user@example.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ])->id;

        Enrollment::create([
            'role' => EnrollmentRole::Teacher,
            'course_id' => $firstCourse,
            'user_id' => $teacherUser,
        ]);

        Enrollment::create([
            'role' => EnrollmentRole::Teacher,
            'course_id' => $secondCourse,
            'user_id' => $teacherUser,
        ]);

        Enrollment::create([
            'role' => EnrollmentRole::Student,
            'course_id' => $secondCourse,
            'user_id' => $studentUser,
        ]);

        Enrollment::create([
            'role' => EnrollmentRole::Student,
            'course_id' => $deactivatedCourse,
            'user_id' => $studentUser,
        ]);
    }
}

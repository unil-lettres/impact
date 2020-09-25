<?php

namespace Database\Seeders;

use App\Enums\CourseType;
use App\Enums\EnrollmentRole;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

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

        $firstCourse = DB::table('courses')->insertGetId([
            'name' => 'First space',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null
        ]);

        $secondCourse = DB::table('courses')->insertGetId([
            'name' => 'Second space',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null
        ]);

        $deactivatedCourse = DB::table('courses')->insertGetId([
            'name' => 'Deactivated space',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => $now
        ]);

        DB::table('courses')->insertGetId([
            'name' => 'External space',
            'type' => CourseType::External,
            'external_id' => 12345678,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);

        $teacherUser = DB::table('users')->insertGetId([
            'name' => 'Teacher user',
            'email' => 'teacher-user@example.com',
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $studentUser = DB::table('users')->insertGetId([
            'name' => 'Student user',
            'email' => 'student-user@example.com',
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now
        ]);

        DB::table('enrollments')->insert([
            'role' => EnrollmentRole::Teacher,
            'course_id' => $firstCourse,
            'user_id' => $teacherUser
        ]);

        DB::table('enrollments')->insert([
            'role' => EnrollmentRole::Teacher,
            'course_id' => $secondCourse,
            'user_id' => $teacherUser
        ]);

        DB::table('enrollments')->insert([
            'role' => EnrollmentRole::Student,
            'course_id' => $secondCourse,
            'user_id' => $studentUser
        ]);

        DB::table('enrollments')->insert([
            'role' => EnrollmentRole::Student,
            'course_id' => $deactivatedCourse,
            'user_id' => $studentUser
        ]);
    }
}

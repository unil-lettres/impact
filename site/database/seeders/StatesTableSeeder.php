<?php

namespace Database\Seeders;

use App\Enums\EnrollmentRole;
use App\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

        $course = DB::table('courses')->insertGetId([
            'name' => 'Test states',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);

        // Create the "private" state
        State::factory()
            ->private()
            ->create([
                'course_id' => $course,
            ]);

        // Create the "open" state
        State::factory()
            ->open()
            ->create([
                'course_id' => $course,
            ]);

        // Create the "public" state
        State::factory()
            ->public()
            ->create([
                'course_id' => $course,
            ]);

        // Create the "archived" state
        State::factory()
            ->archived()
            ->create([
                'course_id' => $course,
            ]);

        $teacherUser = DB::table('users')->insertGetId([
            'name' => 'States teacher user',
            'email' => 'states-teacher-user@example.com',
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $studentUser = DB::table('users')->insertGetId([
            'name' => 'States student user',
            'email' => 'states-student-user@example.com',
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('enrollments')->insert([
            'role' => EnrollmentRole::Teacher,
            'course_id' => $course,
            'user_id' => $teacherUser,
        ]);

        DB::table('enrollments')->insert([
            'role' => EnrollmentRole::Student,
            'course_id' => $course,
            'user_id' => $studentUser,
        ]);
    }
}

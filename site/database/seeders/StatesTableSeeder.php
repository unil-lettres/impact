<?php

namespace Database\Seeders;

use App\Enums\EnrollmentRole;
use App\Enums\StatePermission;
use App\Enums\StateType;
use App\State;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
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
            'deleted_at' => null
        ]);

        // Create the "private" state
        DB::table('states')->insert([
            'name' => trans('states.private'),
            'description' => trans('states.private_description'),
            'position' => 0,
            'type' => StateType::Private,
            'permissions' => State::PERMISSIONS,
            'course_id' => $course,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null
        ]);

        // Create the "open" state
        DB::table('states')->insert([
            'name' => trans('states.open'),
            'description' => trans('states.open_description'),
            'position' => 1,
            'permissions' => '{
                "version": 1,
                "box1": '. StatePermission::TeachersAndEditorsCanShowAndEdit .',
                "box2": '. StatePermission::TeachersAndEditorsCanShowAndEdit .',
                "box3": '. StatePermission::TeachersAndEditorsCanShowAndEdit .',
                "box4": '. StatePermission::TeachersAndEditorsCanShowAndEdit .',
                "box5": '. StatePermission::TeachersAndEditorsCanShowAndEdit .'
            }',
            'course_id' => $course,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null
        ]);

        // Create the "public" state
        DB::table('states')->insert([
            'name' => trans('states.public'),
            'description' => trans('states.public_description'),
            'position' => 2,
            'permissions' => '{
                "version": 1,
                "box1": '. StatePermission::AllCanShowTeachersAndEditorsCanEdit .',
                "box2": '. StatePermission::AllCanShowTeachersAndEditorsCanEdit .',
                "box3": '. StatePermission::AllCanShowTeachersAndEditorsCanEdit .',
                "box4": '. StatePermission::AllCanShowTeachersAndEditorsCanEdit .',
                "box5": '. StatePermission::AllCanShowTeachersAndEditorsCanEdit .'
            }',
            'course_id' => $course,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null
        ]);

        // Create the "archived" state
        DB::table('states')->insert([
            'name' => trans('states.archived'),
            'description' => trans('states.archived_description'),
            'position' => 1000,
            'type' => StateType::Archived,
            'permissions' => '{
                "version": 1,
                "box1": '. StatePermission::TeachersCanShowAndEditEditorsCanShow .',
                "box2": '. StatePermission::TeachersCanShowAndEditEditorsCanShow .',
                "box3": '. StatePermission::TeachersCanShowAndEditEditorsCanShow .',
                "box4": '. StatePermission::TeachersCanShowAndEditEditorsCanShow .',
                "box5": '. StatePermission::TeachersCanShowAndEditEditorsCanShow .'
            }',
            'course_id' => $course,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null
        ]);

        $teacherUser = DB::table('users')->insertGetId([
            'name' => 'States teacher user',
            'email' => 'states-teacher-user@example.com',
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $studentUser = DB::table('users')->insertGetId([
            'name' => 'States student user',
            'email' => 'states-student-user@example.com',
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now
        ]);

        DB::table('enrollments')->insert([
            'role' => EnrollmentRole::Teacher,
            'course_id' => $course,
            'user_id' => $teacherUser
        ]);

        DB::table('enrollments')->insert([
            'role' => EnrollmentRole::Student,
            'course_id' => $course,
            'user_id' => $studentUser
        ]);
    }
}

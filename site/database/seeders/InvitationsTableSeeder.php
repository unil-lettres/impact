<?php

namespace Database\Seeders;

use App\Course;
use App\Enrollment;
use App\Enums\EnrollmentRole;
use App\Invitation;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InvitationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        // Create user with teacher enrollment to test invitations
        $userTeacher = User::create([
            'name' => 'Invitation user teacher',
            'email' => 'invitation-user-teacher@example.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
            'validity' => Carbon::now()->addMonths(config('const.users.validity')),
        ])->id;

        // Create user with student enrollment to test invitations
        $userStudent = User::create([
            'name' => 'Invitation user student',
            'email' => 'invitation-user-student@example.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
            'validity' => Carbon::now()->addMonths(config('const.users.validity')),
        ])->id;

        // Create course to test invitations
        $course = Course::create([
            'name' => 'Invitation space',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ])->id;

        // Create teacher enrollment to test invitations
        Enrollment::create([
            'role' => EnrollmentRole::Teacher,
            'course_id' => $course,
            'user_id' => $userTeacher,
        ]);

        // Create student enrollment to test invitations
        Enrollment::create([
            'role' => EnrollmentRole::Student,
            'course_id' => $course,
            'user_id' => $userStudent,
        ]);

        Invitation::create([
            'email' => 'test-invitation@example.com',
            'invitation_token' => 'b9c757bc9e735ccb9597813cd905631b',
            'registered_at' => null,
            'creator_id' => $userTeacher,
            'course_id' => $course,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Invitation::create([
            'email' => 'test-invitation-registered@example.com',
            'invitation_token' => '544da5bd0f5fd72b880146fed9545cbe',
            'creator_id' => $userTeacher,
            'course_id' => $course,
            'registered_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Invitation::create([
            'email' => 'test-invitation-user@example.com',
            'invitation_token' => '5c10872ae15b1f30d7db409bbf6983f4',
            'creator_id' => $userTeacher,
            'course_id' => $course,
            'registered_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

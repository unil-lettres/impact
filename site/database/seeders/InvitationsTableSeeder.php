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

        // Create user with manager enrollment to test invitations
        $userManager = User::create([
            'name' => 'Invitation user manager',
            'email' => 'invitation-user-manager@example.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now,
            'validity' => Carbon::now()->addMonths(config('const.users.validity')),
        ])->id;

        // Create user with member enrollment to test invitations
        $userMember = User::create([
            'name' => 'Invitation user member',
            'email' => 'invitation-user-member@example.com',
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

        // Create manager enrollment to test invitations
        Enrollment::create([
            'role' => EnrollmentRole::Manager,
            'course_id' => $course,
            'user_id' => $userManager,
        ]);

        // Create member enrollment to test invitations
        Enrollment::create([
            'role' => EnrollmentRole::Member,
            'course_id' => $course,
            'user_id' => $userMember,
        ]);

        Invitation::create([
            'email' => 'test-invitation@example.com',
            'invitation_token' => 'b9c757bc9e735ccb9597813cd905631b',
            'registered_at' => null,
            'creator_id' => $userManager,
            'course_id' => $course,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Invitation::create([
            'email' => 'test-invitation-registered@example.com',
            'invitation_token' => '544da5bd0f5fd72b880146fed9545cbe',
            'creator_id' => $userManager,
            'course_id' => $course,
            'registered_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Invitation::create([
            'email' => 'test-invitation-user@example.com',
            'invitation_token' => '5c10872ae15b1f30d7db409bbf6983f4',
            'creator_id' => $userManager,
            'course_id' => $course,
            'registered_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

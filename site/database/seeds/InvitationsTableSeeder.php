<?php

use Illuminate\Database\Seeder;

class InvitationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon\Carbon::now();

        $user = DB::table('users')->insertGetId([
            'name' => 'Invitation user',
            'email' => 'invitation-user@example.com',
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'created_at' => $now,
            'updated_at' => $now
        ]);

        DB::table('invitations')->insert([
            'email' => 'test-invitation@example.com',
            'invitation_token' => 'b9c757bc9e735ccb9597813cd905631b',
            'registered_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
            'creator_id' => $user
        ]);

        DB::table('invitations')->insert([
            'email' => 'test-invitation-registered@example.com',
            'invitation_token' => '544da5bd0f5fd72b880146fed9545cbe',
            'registered_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
            'creator_id' => $user
        ]);

        DB::table('invitations')->insert([
            'email' => 'test-invitation-user@example.com',
            'invitation_token' => '5c10872ae15b1f30d7db409bbf6983f4',
            'registered_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
            'creator_id' => $user
        ]);
    }
}

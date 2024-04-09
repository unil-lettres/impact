<?php

namespace Database\Seeders;

use App\Card;
use App\Course;
use App\Enrollment;
use App\Enums\EnrollmentRole;
use App\Enums\StateType;
use App\Folder;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CardsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $now = Carbon::now();

        $firstCourse = Course::where('name', 'First space')->first();
        $secondCourse = Course::where('name', 'Second space')->first();
        $testFolder = Folder::where('title', 'Test folder')->first();
        $memberUser = User::where('email', 'member-user@example.com')->first();
        $enrollment = Enrollment::where('course_id', $secondCourse->id)
            ->where('user_id', $memberUser->id)
            ->where('role', EnrollmentRole::Member)
            ->first();

        $testCard = Card::create([
            'title' => 'Test card second space',
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'state_id' => $secondCourse->states
                ->where('type', StateType::Private)
                ->first()->id,
        ])->id;

        $testCardInFolder = Card::create([
            'title' => 'Test card in folder',
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'state_id' => $secondCourse->states
                ->where('type', StateType::Private)
                ->first()->id,
            'folder_id' => $testFolder->id,
        ])->id;

        Card::create([
            'title' => 'Test card second space not assigned',
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'state_id' => $secondCourse->states
                ->where('type', StateType::Private)
                ->first()->id,
        ]);

        Card::create([
            'title' => 'Test card first space',
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $firstCourse->id,
            'state_id' => $firstCourse->states
                ->where('type', StateType::Custom)
                ->first()->id,
        ]);

        Card::create([
            'title' => 'Test card hidden boxes',
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'state_id' => $secondCourse->states
                ->where('type', StateType::Private)
                ->first()->id,
            'options' => [
                'emails' => true,
                'box1' => [
                    'hidden' => true,
                    'link' => null,
                    'start' => null,
                    'end' => null,
                ],
                'box2' => [
                    'hidden' => true,
                    'sync' => true,
                ],
                'box3' => [
                    'hidden' => false,
                    'title' => 'Théorie',
                    'fixed' => false,
                ],
                'box4' => [
                    'hidden' => false,
                    'title' => 'Exemplification',
                    'fixed' => false,
                ],
                'box5' => [
                    'hidden' => true,
                ],
            ],
        ]);

        Card::create([
            'title' => 'Test card features',
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'state_id' => $secondCourse->states
                ->where('type', StateType::Private)
                ->first()->id,
        ]);

        $enrollment->addCard(Card::find($testCard));
        $enrollment->addCard(Card::find($testCardInFolder));
    }
}

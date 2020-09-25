<?php

namespace Database\Seeders;

use App\Card;
use App\Course;
use App\Enrollment;
use App\Enums\EnrollmentRole;
use App\Folder;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $firstCourse = Course::where('name', 'First space')->first();
        $secondCourse = Course::where('name', 'Second space')->first();
        $testFolder = Folder::where('title', 'Test folder')->first();
        $studentUser = User::where('email', 'student-user@example.com')->first();
        $enrollment = Enrollment::where('course_id', $secondCourse->id)
            ->where('user_id', $studentUser->id)
            ->where('role', EnrollmentRole::Student)
            ->first();

        $testCard = DB::table('cards')->insertGetId([
            'title' => 'Test card second space',
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'options' => Card::OPTIONS
        ]);

        $testCardInFolder = DB::table('cards')->insertGetId([
            'title' => 'Test card in folder',
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'options' => Card::OPTIONS,
            'folder_id' => $testFolder->id
        ]);

        DB::table('cards')->insert([
            'title' => 'Test card second space not assigned',
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'options' => Card::OPTIONS
        ]);

        DB::table('cards')->insert([
            'title' => 'Test card first space',
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $firstCourse->id,
            'options' => Card::OPTIONS
        ]);

        $enrollment->addCard(Card::find($testCard));
        $enrollment->addCard(Card::find($testCardInFolder));
    }
}

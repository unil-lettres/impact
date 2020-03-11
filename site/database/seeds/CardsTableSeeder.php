<?php

use App\Course;
use App\Enrollment;
use App\Enums\EnrollmentRole;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

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

        $secondCourse = Course::where('name', 'Second space')->first();
        $studentUser = User::where('email', 'student-user@example.com')->first();
        $enrollment = Enrollment::where('course_id', $secondCourse->id)
            ->where('user_id', $studentUser->id)
            ->where('role', EnrollmentRole::Student)
            ->first();

        $firstCard = DB::table('cards')->insertGetId([
            'title' => 'Test card',
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id
        ]);

        $enrollment->addCard(Enrollment::find($firstCard));
    }
}

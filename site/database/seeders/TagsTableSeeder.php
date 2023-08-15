<?php

namespace Database\Seeders;

use App\Course;
use App\Tag;
use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstCourse = Course::where('name', 'First space')->first();
        $secondCourse = Course::where('name', 'Second space')->first();

        $firstCard = $firstCourse->cards()->where('title', 'Test card first space')->first();
        $secondCard = $secondCourse->cards()->where('title', 'Test card second space')->first();

        $firstTag = Tag::create([
            'name' => 'Test_tag_1_first_course',
            'course_id' => $firstCourse->id,
        ]);

        $secondTag = Tag::create([
            'name' => 'Test_tag_2_first_course',
            'course_id' => $firstCourse->id,
        ]);

        $firstCard->tags()->attach([$firstTag->id, $secondTag->id]);

        $thirdTag = Tag::create([
            'name' => 'Test_tag_second_course',
            'course_id' => $secondCourse->id,
        ]);

        $secondCard->tags()->attach($thirdTag);
    }
}

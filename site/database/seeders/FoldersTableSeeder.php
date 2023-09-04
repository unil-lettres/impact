<?php

namespace Database\Seeders;

use App\Course;
use App\Folder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FoldersTableSeeder extends Seeder
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

        $testFolder = Folder::create([
            'title' => 'Test folder',
            'course_id' => $secondCourse->id,
            'created_at' => $now,
            'updated_at' => $now,
        ])->id;

        Folder::create([
            'title' => 'Test child folder',
            'course_id' => $secondCourse->id,
            'parent_id' => $testFolder,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

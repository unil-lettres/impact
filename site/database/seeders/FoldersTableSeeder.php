<?php

namespace Database\Seeders;

use App\Course;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

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

        $testFolder = DB::table('folders')->insertGetId([
            'title' => 'Test folder',
            'course_id' => $secondCourse->id,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        DB::table('folders')->insert([
            'title' => 'Test child folder',
            'course_id' => $secondCourse->id,
            'parent_id' => $testFolder,
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }
}

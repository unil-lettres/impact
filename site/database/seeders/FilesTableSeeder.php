<?php

namespace Database\Seeders;

use App\Card;
use App\Course;
use App\Enums\FileStatus;
use App\Enums\FileType;
use App\Enums\StateType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FilesTableSeeder extends Seeder
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

        DB::table('files')->insert([
            'name' => 'Test video file',
            'filename' => 'jesuisunfichierdetest1.mp4',
            'status' => FileStatus::Ready,
            'type' => FileType::Video,
            'size' => 4519413,
            'width' => 854,
            'height' => 480,
            'length' => 30,
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'deleted_at' => null,
        ]);

        DB::table('files')->insert([
            'name' => 'Test audio file',
            'filename' => 'jesuisunfichierdetest2.mp3',
            'status' => FileStatus::Transcoding,
            'type' => FileType::Audio,
            'size' => 4519413,
            'width' => null,
            'height' => null,
            'length' => 10,
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $firstCourse->id,
            'deleted_at' => null,
        ]);

        DB::table('files')->insert([
            'name' => 'Deactivated file',
            'filename' => 'jesuisunfichierdetest3.mp3',
            'status' => FileStatus::Ready,
            'type' => FileType::Audio,
            'size' => 4519413,
            'width' => null,
            'height' => null,
            'length' => 10,
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'deleted_at' => $now,
        ]);

        DB::table('files')->insert([
            'name' => 'Failed file',
            'filename' => 'jesuisunfichierdetest4.mp4',
            'status' => FileStatus::Failed,
            'type' => FileType::Video,
            'size' => 4519413,
            'width' => 854,
            'height' => 480,
            'length' => 30,
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'deleted_at' => null,
        ]);

        $usedFile = DB::table('files')->insertGetId([
            'name' => 'Used file',
            'filename' => 'jesuisunfichierdetest5.mp4',
            'status' => FileStatus::Ready,
            'type' => FileType::Video,
            'size' => 4519413,
            'width' => 854,
            'height' => 480,
            'length' => 30,
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'deleted_at' => null,
        ]);

        // Link a file to a card (regular)
        $cardWithFile = DB::table('cards')->insertGetId([
            'title' => 'Test card with file',
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'state_id' => $secondCourse->states
                ->where('type', StateType::Custom)
                ->first()->id,
            'box2' => Card::TRANSCRIPTION,
            'options' => Card::OPTIONS,
            'file_id' => $usedFile,
        ]);

        // Link a card to a file (attachment)
        DB::table('files')->insert([
            'name' => 'My attachment',
            'filename' => 'attachmentstest1.jpg',
            'status' => FileStatus::Ready,
            'type' => FileType::Image,
            'size' => 34661,
            'width' => null,
            'height' => null,
            'length' => null,
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'deleted_at' => null,
            'card_id' => $cardWithFile,
        ]);
    }
}

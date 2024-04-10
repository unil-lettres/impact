<?php

namespace Database\Seeders;

use App\Card;
use App\Course;
use App\Enums\FileStatus;
use App\Enums\FileType;
use App\Enums\StateType;
use App\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $firstCourse = Course::where('name', 'First space')->first();
        $secondCourse = Course::where('name', 'Second space')->first();

        File::create([
            'name' => 'Test video file',
            'filename' => 'jesuisunfichierdetest1.mp4',
            'status' => FileStatus::Ready,
            'progress' => 100,
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

        $processingFile = File::create([
            'name' => 'Test audio file',
            'filename' => 'jesuisunfichierdetest2.mp3',
            'status' => FileStatus::Transcoding,
            'progress' => 50,
            'type' => FileType::Audio,
            'size' => 4519413,
            'width' => null,
            'height' => null,
            'length' => 10,
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $firstCourse->id,
            'deleted_at' => null,
        ])->id;

        Card::create([
            'title' => 'Test card with processing file',
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'state_id' => $secondCourse->states
                ->where('type', StateType::Custom)
                ->first()->id,
            'file_id' => $processingFile,
        ]);

        File::create([
            'name' => 'Deactivated file',
            'filename' => 'jesuisunfichierdetest3.mp3',
            'status' => FileStatus::Ready,
            'progress' => 100,
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

        $failedFile = File::create([
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
        ])->id;

        Card::create([
            'title' => 'Test card with failed file',
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'state_id' => $secondCourse->states
                ->where('type', StateType::Custom)
                ->first()->id,
            'file_id' => $failedFile,
        ]);

        $readyFile = File::create([
            'name' => 'Used file',
            'filename' => 'jesuisunfichierdetest5.mp4',
            'status' => FileStatus::Ready,
            'progress' => 100,
            'type' => FileType::Video,
            'size' => 4519413,
            'width' => 854,
            'height' => 480,
            'length' => 30,
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'deleted_at' => null,
        ])->id;

        // Link a file to a card (regular)
        $cardWithFile = Card::create([
            'title' => 'Test card with file',
            'created_at' => $now,
            'updated_at' => $now,
            'course_id' => $secondCourse->id,
            'state_id' => $secondCourse->states
                ->where('type', StateType::Custom)
                ->first()->id,
            'file_id' => $readyFile,
        ])->id;

        // Link a card to a file (attachment)
        File::create([
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

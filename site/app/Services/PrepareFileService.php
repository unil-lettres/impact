<?php

namespace App\Services;

use App\Card;
use App\Course;
use App\Enums\FileStatus;
use App\File;
use Illuminate\Http\UploadedFile;

class PrepareFileService
{
    private ?Course $course;

    private ?Card $card;

    private bool $attachment;

    private FileUploadProcessor $fileUploadProcessor;

    public function __construct(Course $course, Card $card, bool $attachment)
    {
        $this->course = $course;
        $this->card = $card;
        $this->attachment = $attachment;
        $this->fileUploadProcessor = new FileUploadProcessor;
    }

    /**
     * Prepare file before processing.
     */
    public function prepareFile(UploadedFile $uploadedFile): File
    {
        // Move file to temp storage
        $path = $this->fileUploadProcessor
            ->moveFileToStoragePath(
                $uploadedFile,
                true
            );

        // Create file draft
        return $this->createFileDraft(
            $path,
            $this->course,
            $uploadedFile
        );
    }

    /**
     * Add the appropriate relation to the file if needed
     */
    public function addRelation(File $file): void
    {
        if ($this->attachment) {
            // If the file is an attachment, update
            // the file and link it to the card
            $this->updateFile($file, $this->card);
        } elseif ($this->card) {
            // If the file is not an attachment, update
            // the card and link it to the file
            $this->updateCard($file, $this->card);
        }
    }

    /**
     * Create file draft with basic infos
     */
    private function createFileDraft(string $path, ?Course $course, UploadedFile $uploadedFile): File
    {
        // Get file basic infos
        $mimeType = $uploadedFile->getMimeType();
        $filename = $uploadedFile->getClientOriginalName();
        $size = $uploadedFile->getSize();

        $course_id = $course?->id;

        return File::create([
            'name' => $this->fileUploadProcessor
                ->getFileName($filename),
            'filename' => $this->fileUploadProcessor
                ->getBaseName($path),
            'status' => FileStatus::Processing,
            'type' => $this->fileUploadProcessor
                ->fileType($mimeType),
            'size' => $size,
            'course_id' => $course_id,
        ]);
    }

    /**
     * Link the file to a card (box1 media)
     */
    private function updateCard(File $file, Card $card): void
    {
        $card->update([
            'file_id' => $file->id,
        ]);
        $card->save();
    }

    /**
     * Link the card to the file (make an attachment)
     */
    private function updateFile(File $file, Card $card): void
    {
        $file->update([
            'card_id' => $card->id,
        ]);
        $file->save();
    }
}

<?php

namespace App\Services;

use App\Card;
use App\Course;
use App\Enums\FileStatus;
use App\File;
use Illuminate\Http\UploadedFile;

class FileService
{
    private ?Course $course;

    private ?Card $card;

    private bool $attachment;

    private FileStorageService $fileStorageService;

    public function __construct(?Course $course, ?Card $card, bool $attachment)
    {
        $this->course = $course;
        $this->card = $card;
        $this->attachment = $attachment;
        $this->fileStorageService = new FileStorageService();
    }

    /**
     * Prepare file before processing.
     */
    public function prepareFile(UploadedFile $uploadedFile): File
    {
        // Move file to temp storage
        $path = $this->fileStorageService
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
        if (! $this->card) {
            return;
        }

        match ($this->attachment) {
            // If the file is an attachment, update
            // the file and link it to the card
            true => $this->updateFile($file, $this->card),
            // If the file is a regular file, update
            // the card and link it to the file
            default => $this->updateCard($file, $this->card),
        };
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
            'name' => $this->fileStorageService
                ->getFileName($filename),
            'filename' => $this->fileStorageService
                ->getBaseName($path),
            'status' => FileStatus::Processing,
            'type' => $this->fileStorageService
                ->fileType($mimeType),
            'size' => $size,
            'course_id' => $course_id,
        ]);
    }

    /**
     * Link a file to a card
     */
    private function updateCard(File $file, Card $card): void
    {
        $card->update([
            'file_id' => $file->id,
        ]);
    }

    /**
     * Link a card to a file
     */
    private function updateFile(File $file, Card $card): void
    {
        $file->update([
            'card_id' => $card->id,
        ]);
    }
}

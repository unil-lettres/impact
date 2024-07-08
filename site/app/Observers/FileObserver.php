<?php

namespace App\Observers;

use App\File;
use App\Services\FileStorageService;

class FileObserver
{
    /**
     * Handle the file "deleted" event.
     */
    public function deleted(File $file): void
    {
        if ($file->isForceDeleting()) {
            // Remove the binary associated with the file record
            $fileStorageService = new FileStorageService();
            $fileStorageService->removeFileFromStandardStorage($file->filename);
        }
    }
}

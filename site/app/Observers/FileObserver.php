<?php

namespace App\Observers;

use App\File;
use App\Services\FileUploadService;

class FileObserver
{
    /**
     * Handle the file "deleted" event.
     *
     * @return void
     */
    public function deleted(File $file)
    {
        if ($file->isForceDeleting()) {
            // Remove the binary associated with the file record
            $fileUploadService = new FileUploadService();
            $fileUploadService
                ->removeFileFromStandardStorage($file->filename);
        }
    }
}

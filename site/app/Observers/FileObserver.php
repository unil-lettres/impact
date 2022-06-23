<?php

namespace App\Observers;

use App\File;
use App\Services\FileUploadProcessor;

class FileObserver
{
    /**
     * Handle the file "deleted" event.
     *
     * @param File $file
     *
     * @return void
     */
    public function deleted(File $file)
    {
        if ($file->isForceDeleting()) {
            // Remove the binary file associated with the file record
            $fileUploadProcessor = new FileUploadProcessor();
            $fileUploadProcessor
                ->removeFileFromStandardStorage($file->filename);
        }
    }
}

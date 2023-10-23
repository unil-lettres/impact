<?php

namespace App\Services\Clone;

use App\Enums\StoragePath;
use App\File;
use Illuminate\Support\Facades\Storage;

class CloneFileService
{
    private File $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Clone a file and return it.
     *
     * @param  string  $prefix Prefix to add to the filename.
     */
    public function clone(string $prefix = ''): ?File
    {
        // Clean filename to keep only the name of the file
        $cleanedFilename = pathinfo($this->file->filename, PATHINFO_BASENAME);
        $copiedFilename = substr($prefix.$cleanedFilename, 0, 99);

        $success = Storage::disk('public')->copy(
            StoragePath::UploadStandard.'/'.$cleanedFilename,
            StoragePath::UploadStandard.'/'.$copiedFilename,
        );

        if (! $success) {
            return null;
        }

        $file = $this->file->replicate()->fill(['filename' => $copiedFilename]);
        $file->save();

        return $file;
    }
}

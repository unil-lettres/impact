<?php

namespace App\Services;

use App\Enums\FileType;
use App\Enums\StoragePath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    public function __construct()
    {
        Storage::disk('public')
            ->makeDirectory(StoragePath::UploadStandard);

        Storage::disk('public')
            ->makeDirectory(StoragePath::UploadTemp);
    }

    /**
     * Return file type from given mime type.
     */
    public function fileType(string $mimeType): string
    {
        if (Str::is('video/*', $mimeType)) {
            return FileType::Video;
        }

        if (Str::is('audio/*', $mimeType)) {
            return FileType::Audio;
        }

        if (Str::is('image/*', $mimeType)) {
            return FileType::Image;
        }

        if (Str::is('application/*', $mimeType)) {
            return FileType::Document;
        }

        return FileType::Other;
    }

    /**
     * Get basename (ex. my_file.mp4) from file path.
     */
    public function getBaseName($filePath): string
    {
        return pathinfo($filePath, PATHINFO_BASENAME);
    }

    /**
     * Get filename (ex. my_file) from file path.
     */
    public function getFileName($filePath): string
    {
        if (! $filePath) {
            return 'No name';
        }

        return pathinfo($filePath, PATHINFO_FILENAME);
    }

    /**
     * Move file to defined storage path.
     */
    public function moveFileToStoragePath(UploadedFile $file, bool $isTemp = false): string|false
    {
        $path = $isTemp ? StoragePath::UploadTemp : StoragePath::UploadStandard;

        return $file->store(
            $path, 'public'
        );
    }

    /**
     * Move file from temp storage path to standard storage path.
     */
    public function moveFileToStandardStorage(string $filename): bool
    {
        // Clean filename to keep only the name of the file
        $cleanedFilename = $this->getBaseName($filename);

        return Storage::disk('public')
            ->move(
                StoragePath::UploadTemp.'/'.$cleanedFilename,
                StoragePath::UploadStandard.'/'.$cleanedFilename
            );
    }

    /**
     * Get the size of a file.
     */
    public function getFileSize(string $filename, bool $isTemp = false): int
    {
        // Clean filename to keep only the name of the file
        $cleanedFilename = $this->getBaseName($filename);

        $path = $isTemp ? StoragePath::UploadTemp : StoragePath::UploadStandard;

        return Storage::disk('public')
            ->size(
                $path.'/'.$cleanedFilename
            );
    }

    /**
     * Remove file from temp storage.
     */
    public function removeFileFromTempStorage(string $filename): bool
    {
        // Clean filename to keep only the name of the file
        $cleanedFilename = $this->getBaseName($filename);

        return Storage::disk('public')
            ->delete(
                StoragePath::UploadTemp.'/'.$cleanedFilename
            );
    }

    /**
     * Remove file from standard storage.
     */
    public function removeFileFromStandardStorage(string $filename): bool
    {
        // Clean filename to keep only the name of the file
        $cleanedFilename = $this->getBaseName($filename);

        return Storage::disk('public')
            ->delete(
                StoragePath::UploadStandard.'/'.$cleanedFilename
            );
    }
}
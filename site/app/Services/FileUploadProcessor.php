<?php

namespace App\Services;

use App\Enums\FileType;
use App\Enums\StoragePath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadProcessor
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
     *
     * @param  string  $mimeType
     * @return string
     */
    public function fileType(string $mimeType)
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
     *
     * @param $filePath
     * @return string
     */
    public function getBaseName($filePath)
    {
        return pathinfo($filePath, PATHINFO_BASENAME);
    }

    /**
     * Get filename (ex. my_file) from file path.
     *
     * @param $filePath
     * @return string
     */
    public function getFileName($filePath)
    {
        if (! $filePath) {
            return 'No name';
        }

        return pathinfo($filePath, PATHINFO_FILENAME);
    }

    /**
     * Move file to defined storage path.
     *
     * @param  UploadedFile  $file
     * @param  bool  $isTemp
     * @return string|false
     */
    public function moveFileToStoragePath(UploadedFile $file, $isTemp = false)
    {
        $path = $isTemp ? StoragePath::UploadTemp : StoragePath::UploadStandard;

        return $file->store(
            $path, 'public'
        );
    }

    /**
     * Move file from temp storage path to standard storage path.
     *
     * @param  string  $filename
     * @return bool
     */
    public function moveFileToStandardStorage(string $filename)
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
     *
     * @param  string  $filename
     * @param  bool  $isTemp
     * @return int
     */
    public function getFileSize(string $filename, $isTemp = false)
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
     *
     * @param  string  $filename
     * @return bool
     */
    public function removeFileFromTempStorage(string $filename)
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
     *
     * @param  string  $filename
     * @return bool
     */
    public function removeFileFromStandardStorage(string $filename)
    {
        // Clean filename to keep only the name of the file
        $cleanedFilename = $this->getBaseName($filename);

        return Storage::disk('public')
            ->delete(
                StoragePath::UploadStandard.'/'.$cleanedFilename
            );
    }
}

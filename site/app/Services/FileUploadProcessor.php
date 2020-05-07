<?php

namespace App\Services;

use App\Enums\FileType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadProcessor
{
    /**
     * Return path to main file storage.
     *
     * @return string
     */
    public function standardFileStoragePath()
    {
        return '/uploads/files';
    }

    /**
     * Return path to temporary file storage.
     *
     * @return string
     */
    public function tempFileStoragePath()
    {
        return '/uploads/tmp';
    }

    /**
     * Return file type from given mime type.
     *
     * @param string $mimeType
     *
     * @return string
     */
    public function fileType(string $mimeType) {
        if(Str::is('video/*', $mimeType)) {
            return FileType::Video;
        }

        if(Str::is('audio/*', $mimeType)) {
            return FileType::Audio;
        }

        if(Str::is('image/*', $mimeType)) {
            return FileType::Image;
        }

        if(Str::is('application/*', $mimeType)) {
            return FileType::Document;
        }

        return FileType::Other;
    }

    /**
     * Get basename (ex. my_file.mp4) from file path.
     *
     * @param $filePath
     *
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
     *
     * @return string
     */
    public function getFileName($filePath)
    {
        if(!$filePath) {
            return 'No name';
        }

        return pathinfo($filePath, PATHINFO_FILENAME);
    }

    /**
     * Move file to defined storage path.
     *
     * @param UploadedFile $file
     * @param boolean $isTemp
     *
     * @return string|false
     */
    public function moveFileToStoragePath(UploadedFile $file, $isTemp = false)
    {
        $path = $isTemp ? $this->tempFileStoragePath() : $this->standardFileStoragePath();

        return $file->store(
            $path, 'public'
        );
    }

    /**
     * Move file from temp storage path to standard storage path.
     *
     * @param string $filename
     *
     * @return boolean
     */
    public function moveFileToStandardStorage(string $filename)
    {
        // Clean filename to keep only the name of the file
        $cleanedFilename = $this->getBaseName($filename);

        return Storage::disk('public')
            ->move(
                $this->tempFileStoragePath() . '/' . $cleanedFilename,
                $this->standardFileStoragePath() . '/' . $cleanedFilename
            );
    }

    /**
     * Remove file from temp storage.
     *
     * @param string $filename
     *
     * @return boolean
     */
    public function removeFileFromTempStorage(string $filename)
    {
        // Clean filename to keep only the name of the file
        $cleanedFilename = $this->getBaseName($filename);

        return Storage::disk('public')
            ->delete(
                $this->tempFileStoragePath() . '/' . $cleanedFilename
            );
    }
}

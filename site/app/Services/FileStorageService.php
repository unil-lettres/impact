<?php

namespace App\Services;

use App\Enums\FileType;
use App\Enums\StoragePath;
use App\File;
use FFMpeg\FFProbe;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService
{
    public string $fullTempPath;

    public string $fullStandardPath;

    public function __construct()
    {
        Storage::disk('public')
            ->makeDirectory(StoragePath::UploadStandard);
        Storage::disk('public')
            ->makeDirectory(StoragePath::UploadTemp);

        $this->fullTempPath = Storage::disk('public')
            ->path('uploads/tmp/');
        $this->fullStandardPath = Storage::disk('public')
            ->path('uploads/files/');
    }

    /**
     * Return file type from given mime type.
     */
    public function fileType(string $mimeType, string $fileFullTempPath): string
    {
        if (Str::is('audio/*', $mimeType) || Str::is('video/*', $mimeType)) {
            return $this->probeType(
                $fileFullTempPath
            );
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

    /**
     * Clone a file and return it.
     */
    public function clone(File $file, string $prefix = ''): ?File
    {
        // Clean filename to keep only the name of the file
        $cleanedFilename = $this->getBaseName($file->filename);
        $copiedFilename = substr($prefix.$cleanedFilename, 0, 99);

        $success = Storage::disk('public')->copy(
            StoragePath::UploadStandard.'/'.$cleanedFilename,
            StoragePath::UploadStandard.'/'.$copiedFilename,
        );

        if (! $success) {
            return null;
        }

        $file = $file->replicate()->fill(['filename' => $copiedFilename]);
        $file->save();

        return $file;
    }

    /**
     * Check for audio/video tracks to determine file type.
     */
    private function probeType(string $fileFullTempPath): string
    {
        $ffprobe = FFProbe::create();

        // Get number of video track(s)
        $videoTracks = array_filter(
            $ffprobe
                ->streams($fileFullTempPath)
                ->videos()
                ->all(),

            // Filter covers tracks.
            fn($stream) => $stream->get('disposition')['attached_pic'] !== 1
        );

        // Get number of audio track(s)
        $audioTracks = $ffprobe
            ->streams($fileFullTempPath)
            ->audios()
            ->count();

        if (count($videoTracks) > 0) {
            return FileType::Video;
        }

        if ($audioTracks > 0) {
            return FileType::Audio;
        }

        return FileType::Other;
    }
}

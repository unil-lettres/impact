<?php

namespace App\Jobs;

use App\Enums\FileStatus;
use App\Enums\FileType;
use App\File;
use App\Services\FileUploadService;
use Exception;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Video\X264;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected File $file;

    protected FileUploadService $fileUploadService;

    protected string $fullTempPath;

    protected string $fullStandardPath;

    /**
     * Number of seconds the job can run before timing out.
     */
    public int $timeout;

    /**
     * Create a new job instance.
     */
    public function __construct(File $file)
    {
        $this->file = $file;
        $this->fileUploadService = new FileUploadService();
        $this->fullTempPath = Storage::disk('public')
            ->path('uploads/tmp/');
        $this->fullStandardPath = Storage::disk('public')
            ->path('uploads/files/');
        $this->timeout = config('const.files.ffmpeg.timeout');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->file->isAttachment()) {
            // If the file is an attachment, always process
            // it as a document (avoid transcoding)
            $this->processDocument();
        } else {
            // Otherwise Launch the process corresponding
            // to the file type
            match ($this->file->type) {
                FileType::Audio => $this->processAudio(),
                FileType::Video => $this->processVideo(),
                FileType::Image => $this->processImage(),
                default => $this->processDocument(),
            };
        }
    }

    /**
     * The job failed to process.
     */
    public function failed(Exception $exception): void
    {
        Log::error($exception->getMessage());

        $this->file->update([
            'status' => FileStatus::Failed,
        ]);
        $this->file->save();

        $this->fileUploadService
            ->removeFileFromTempStorage($this->file->filename);
    }

    /**
     * Process an image.
     */
    protected function processImage(): void
    {
        $this->fileUploadService
            ->moveFileToStandardStorage($this->file->filename);

        $this->file->update([
            'status' => FileStatus::Ready,
        ]);
    }

    /**
     * Process a document.
     */
    protected function processDocument(): void
    {
        $this->fileUploadService
            ->moveFileToStandardStorage($this->file->filename);

        $this->file->update([
            'status' => FileStatus::Ready,
        ]);
        $this->file->save();
    }

    /**
     * Process a video file.
     */
    protected function processVideo(): void
    {
        $this->transcodeFile(FileType::Video);
    }

    /**
     * Process an audio file.
     */
    protected function processAudio(): void
    {
        $this->transcodeFile(FileType::Audio);
    }

    /**
     * Transcode a media file with FFmpeg.
     */
    protected function transcodeFile(string $type): void
    {
        $this->file->update([
            'status' => FileStatus::Transcoding,
        ]);
        $this->file->save();

        match ($type) {
            FileType::Video => $this->transcodeVideo(),
            default => $this->transcodeAudio(),
        };

        $this->file->update([
            'status' => FileStatus::Ready,
            'progress' => 100,
        ]);
    }

    /**
     * Transcode a video file.
     */
    protected function transcodeVideo(): void
    {
        $ffmpeg = FFMpeg::create(
            [
                'timeout' => config('const.files.ffmpeg.timeout'),
            ]
        );
        $ffprobe = FFProbe::create();
        $openFromPathname = $this->fullTempPath.$this->file->filename;
        $saveToPathname = $this->fullStandardPath.$this->fileUploadService
            ->getFileName($this->file->filename).'.'.config('const.files.video.extension');

        $format = new X264('libmp3lame', 'libx264');
        $format->on('progress', function ($video, $format, $progress) {
            // Update file progress in database
            // every 10% of transcoding
            if ($progress % 10 === 0) {
                $this->file->update([
                    'progress' => $progress,
                ]);
            }
        });

        // Transcode to MP4/X264 with FFmpeg
        $video = $ffmpeg
            ->open($openFromPathname);
        $video
            ->filters()
            ->resize(
                new Dimension(
                    config('const.files.video.width'),
                    config('const.files.video.height')
                ),
                ResizeFilter::RESIZEMODE_SCALE_WIDTH
            )
            ->synchronize();
        $video
            ->save(
                $format,
                $saveToPathname,
            );

        // Remove uploaded file from temp storage
        $this->fileUploadService
            ->removeFileFromTempStorage($video->getPathfile());

        // Update file properties in database
        $videoStream = $ffprobe
            ->streams($saveToPathname)
            ->videos()
            ->first();
        if ($videoStream) {
            $this->file->update([
                'filename' => $this->fileUploadService
                    ->getBaseName($saveToPathname),
                'size' => $this->fileUploadService
                    ->getFileSize($saveToPathname),
                'length' => (int) $videoStream
                    ->get('duration'),
                'width' => $videoStream
                    ->getDimensions()
                    ->getWidth(),
                'height' => $videoStream
                    ->getDimensions()
                    ->getHeight(),
            ]);
        }
    }

    /**
     * Transcode an audio file.
     */
    protected function transcodeAudio(): void
    {
        $ffmpeg = FFMpeg::create(
            [
                'timeout' => config('const.files.ffmpeg.timeout'),
            ]
        );
        $ffprobe = FFProbe::create();
        $openFromPathname = $this->fullTempPath.$this->file->filename;
        $saveToPathname = $this->fullStandardPath.$this->fileUploadService
            ->getFileName($this->file->filename).'.'.config('const.files.audio.extension');

        $format = new Mp3();
        $format->on('progress', function ($audio, $format, $progress) {
            // Update file progress in database
            // every 10% of transcoding
            if ($progress % 10 === 0) {
                $this->file->update([
                    'progress' => $progress,
                ]);
            }
        });

        // Transcode to MP3 with FFmpeg
        $audio = $ffmpeg
            ->open($openFromPathname);
        $audio
            ->save(
                $format,
                $saveToPathname,
            );

        // Remove uploaded file from temp storage
        $this->fileUploadService
            ->removeFileFromTempStorage($audio->getPathfile());

        // Update file properties in database
        $audioStream = $ffprobe
            ->streams($saveToPathname)
            ->audios()
            ->first();
        if ($audioStream) {
            $this->file->update([
                'filename' => $this->fileUploadService
                    ->getBaseName($saveToPathname),
                'size' => $this->fileUploadService
                    ->getFileSize($saveToPathname),
                'length' => (int) $audioStream
                    ->get('duration'),
            ]);
        }
    }
}

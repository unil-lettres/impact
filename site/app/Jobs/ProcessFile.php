<?php

namespace App\Jobs;

use App\Enums\FileStatus;
use App\Enums\FileType;
use App\File;
use App\Services\FileUploadProcessor;
use Exception;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\Video\X264;
use FFMpeg\Format\Audio\Mp3;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected File $file;
    protected FileUploadProcessor $fileUploadProcessor;
    protected string $fullTempPath;
    protected string $fullStandardPath;

    /**
     * Create a new job instance.
     *
     * @param File $file
     *
     * @return void
     */
    public function __construct(File $file)
    {
        $this->file = $file;
        $this->fileUploadProcessor = new FileUploadProcessor();
        $this->fullTempPath = Storage::disk('public')
            ->path('uploads/tmp/');
        $this->fullStandardPath = Storage::disk('public')
            ->path('uploads/files/');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Launch the process corresponding to the file type
        switch ($this->file->type) {
            case FileType::Audio:
                $this->processAudio();
                break;
            case FileType::Video:
                $this->processVideo();
                break;
            case FileType::Image:
                $this->processImage();
                break;
            case FileType::Document:
            default:
                $this->processDocument();
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     *
     * @return void
     */
    public function failed(Exception $exception)
    {
        $this->file->update([
            'status' => FileStatus::Failed
        ]);
        $this->file->save();

        $this->fileUploadProcessor
            ->removeFileFromTempStorage($this->file->filename);
    }

    /**
     * Process an image.
     */
    protected function processImage() {
        $this->fileUploadProcessor
            ->moveFileToStandardStorage($this->file->filename);

        $this->file->update([
            'status' => FileStatus::Ready
        ]);
        $this->file->save();
    }

    /**
     * Process a document.
     */
    protected function processDocument() {
        $this->fileUploadProcessor
            ->moveFileToStandardStorage($this->file->filename);

        $this->file->update([
            'status' => FileStatus::Ready
        ]);
        $this->file->save();
    }

    /**
     * Process a video file.
     */
    protected function processVideo() {
        $this->transcodeFile(FileType::Video);
    }

    /**
     * Process an audio file.
     */
    protected function processAudio() {
        $this->transcodeFile(FileType::Audio);
    }

    /**
     * Transcode a media file with FFmpeg.
     *
     * @param string $type
     */
    protected function transcodeFile(string $type) {
        $this->file->update([
            'status' => FileStatus::Transcoding
        ]);
        $this->file->save();

        switch ($type) {
            case FileType::Video:
                $this->transcodeVideo();
                break;
            case FileType::Audio:
            default:
                $this->transcodeAudio();
        }

        $this->file->update([
            'status' => FileStatus::Ready
        ]);
        $this->file->save();
    }

    /**
     * Transcode a video file.
     */
    protected function transcodeVideo() {
        $ffmpeg = FFMpeg::create();
        $ffprobe = FFProbe::create();
        $openFromPathname = $this->fullTempPath . $this->file->filename;
        $saveToPathname = $this->fullStandardPath . $this->fileUploadProcessor
                ->getFileName($this->file->filename) . '.' . config('const.files.video.extension');

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
                new X264(),
                $saveToPathname,
            );

        // Remove uploaded file from temp storage
        $this->fileUploadProcessor
            ->removeFileFromTempStorage($video->getPathfile());

        // Update file properties in database
        $videoStream = $ffprobe
            ->streams($saveToPathname)
            ->videos()
            ->first();
        if($videoStream) {
            $this->file->update([
                'filename' => $this->fileUploadProcessor
                    ->getBaseName($saveToPathname),
                'length' => (int)$videoStream
                    ->get('duration'),
                'width' => $videoStream
                    ->getDimensions()
                    ->getWidth(),
                'height' => $videoStream
                    ->getDimensions()
                    ->getHeight()
            ]);
            $this->file->save();
        }
    }

    /**
     * Transcode an audio file.
     */
    protected function transcodeAudio() {
        $ffmpeg = FFMpeg::create();
        $ffprobe = FFProbe::create();
        $openFromPathname = $this->fullTempPath . $this->file->filename;
        $saveToPathname = $this->fullStandardPath . $this->fileUploadProcessor
                ->getFileName($this->file->filename) . '.' . config('const.files.audio.extension');

        // Transcode to MP3 with FFmpeg
        $audio = $ffmpeg
            ->open($openFromPathname);
        $audio
            ->save(
                new Mp3(),
                $saveToPathname,
            );

        // Remove uploaded file from temp storage
        $this->fileUploadProcessor
            ->removeFileFromTempStorage($audio->getPathfile());

        // Update file properties in database
        $audioStream = $ffprobe
            ->streams($saveToPathname)
            ->audios()
            ->first();
        if($audioStream) {
            $this->file->update([
                'filename' => $this->fileUploadProcessor
                    ->getBaseName($saveToPathname),
                'length' => (int)$audioStream
                    ->get('duration')
            ]);
            $this->file->save();
        }
    }
}

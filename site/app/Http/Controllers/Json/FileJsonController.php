<?php

namespace App\Http\Controllers\Json;

use App\Card;
use App\Course;
use App\Enums\FileStatus;
use App\File;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessFile;
use App\Services\FileUploadProcessor;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileJsonController extends Controller
{
    /**
     * File upload endpoint.
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function upload(Request $request, FileUploadProcessor $fileUploadProcessor)
    {
        $course = $request->get('course') ?
            Course::find($request->get('course')) : null;
        $card = $request->get('card') ?
            Card::find($request->get('card')) : null;

        $this->authorize('upload', [
            File::class,
            $course,
            $card,
        ]);

        // Move file to temp storage
        $path = $fileUploadProcessor
            ->moveFileToStoragePath(
                $request->file('file'),
                true
            );

        // Create file draft
        $file = $this->createFileDraft(
            $fileUploadProcessor,
            $request,
            $path,
            $course
        );

        if ($card) {
            // Optionally link the file to a card
            $this->updateCard($file, $card);
        }

        // Dispatch record for async file processing
        ProcessFile::dispatch($file);

        return response()->json([
            'success' => $file->id,
        ], 200);
    }

    /**
     * Create file draft with basic infos
     *
     * @return File $file
     */
    private function createFileDraft(FileUploadProcessor $fileUploadProcessor, Request $request, string $path, ?Course $course)
    {
        // Get file basic infos
        $mimeType = $request->file('file')->getMimeType();
        $filename = $request->file('file')->getClientOriginalName();
        $size = $request->file('file')->getSize();

        $course_id = $course ? $course->id : null;

        return File::create([
            'name' => $fileUploadProcessor
                ->getFileName($filename),
            'filename' => $fileUploadProcessor
                ->getBaseName($path),
            'status' => FileStatus::Processing,
            'type' => $fileUploadProcessor
                ->fileType($mimeType),
            'size' => $size,
            'course_id' => $course_id,
        ]);
    }

    /**
     * Link the file to a card
     *
     * @return void
     */
    private function updateCard(File $file, Card $card)
    {
        $card->update([
            'file_id' => $file->id,
        ]);
        $card->save();
    }
}

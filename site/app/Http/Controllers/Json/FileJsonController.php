<?php

namespace App\Http\Controllers\Json;

use App\Card;
use App\Course;
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

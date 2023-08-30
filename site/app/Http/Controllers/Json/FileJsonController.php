<?php

namespace App\Http\Controllers\Json;

use App\Card;
use App\Course;
use App\File;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpload;
use App\Jobs\ProcessFile;
use App\Services\PrepareFileService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class FileJsonController extends Controller
{
    /**
     * File upload endpoint.
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function upload(StoreUpload $request)
    {
        $course = $request->get('course_id') ?
            Course::find($request->get('course_id')) : null;
        $card = $request->get('card_id') ?
            Card::find($request->get('card_id')) : null;
        $attachment = $request->get('attachment');

        $this->authorize('upload', [
            File::class,
            $course,
            $card,
        ]);

        // Create file draft, move to temp storage and add the
        // appropriate relation (regular or attachment)
        $fileService = new PrepareFileService(
            $course,
            $card,
            $attachment
        );
        $file = $fileService->prepareFile(
            $request->file('file')
        );
        $fileService->addRelation(
            $file
        );

        // Dispatch record for async file processing
        ProcessFile::dispatch($file);

        return response()->json([
            'success' => $file->id,
        ], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Card;
use App\Course;
use App\Enums\FileStatus;
use App\File;
use App\Http\Requests\DestroyFile;
use App\Http\Requests\EditFile;
use App\Jobs\ProcessFile;
use App\Services\FileUploadProcessor;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // TODO: add policy
        // TODO: add logic
    }

    /**
     * Display a listing of the resource in the admin panel.
     *
     * @return Renderable
     */
    public function manage()
    {
        // TODO: add policy

        $files = File::orderBy('created_at', 'desc')
            ->paginate(config('const.pagination.per'));

        return view('files.manage', [
            'files' => $files
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        // TODO: add policy

        $courses = Course::all();

        return view('files.create', [
            'courses' => $courses
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // TODO: add policy
        // TODO: add logic
    }

    /**
     * Display the specified resource.
     *
     * @param File $file
     * @return \Illuminate\Http\Response
     */
    public function show(File $file)
    {
        // TODO: add policy
        // TODO: add logic
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param EditFile $user
     * @param int $id
     *
     * @return Renderable
     */
    public function edit(EditFile $user, int $id)
    {
        $file = File::find($id);

        // TODO: add policy

        return view('files.edit', [
            'file' => $file,
            'courses' => Course::all(),
            'cards' => $file
                ->cards()
                ->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param File $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        // TODO: add policy
        // TODO: add logic
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyFile $request
     * @param int $id
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy(DestroyFile $request, int $id)
    {
        $file = File::find($id);

        $this->authorize('forceDelete', $file);

        // Delete the record
        $file->forceDelete();

        // Then the binary file will be deleted in the FileObserver "deleted" event

        return redirect()
            ->back()
            ->with('success', trans('messages.file.deleted'));
    }

    /**
     * File upload endpoint.
     *
     * @param Request $request
     * @param FileUploadProcessor $fileUploadProcessor
     *
     * @return JsonResponse
     */
    public function upload(Request $request, FileUploadProcessor $fileUploadProcessor)
    {
        $course = $request->get('course') ?
            Course::find($request->get('course')) : null;
        $card = $request->get('card') ?
            Card::find($request->get('card')) : null;

        // TODO: add policy

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

        if($card) {
            // Optionally link the file to a card
            $this->updateCard($file, $card);
        }

        // Dispatch record for async file processing
        ProcessFile::dispatch($file);

        return response()->json([
            'success' => $file->id
        ], 200);
    }

    /**
     * Create file draft with basic infos
     *
     * @param FileUploadProcessor $fileUploadProcessor
     * @param Request $request
     * @param string $path
     * @param Course|null $course
     *
     * @return File $file
     */
    private function createFileDraft(FileUploadProcessor $fileUploadProcessor, Request $request, string $path, ?Course $course) {
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
            'course_id' => $course_id
        ]);
    }

    /**
     * Link the file to a card
     *
     * @param File $file
     * @param Card $card
     *
     * @return void
     */
    private function updateCard(File $file, Card $card) {
        $card->update([
            'file_id' => $file->id
        ]);
        $card->save();
    }
}

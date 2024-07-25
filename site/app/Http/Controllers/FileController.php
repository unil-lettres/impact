<?php

namespace App\Http\Controllers;

use App\Card;
use App\Course;
use App\Enums\FileStatus;
use App\File;
use App\Http\Requests\DestroyFile;
use App\Http\Requests\DownloadFile;
use App\Http\Requests\EditFile;
use App\Http\Requests\ManageFiles;
use App\Http\Requests\UpdateFile;
use App\Services\FileStorageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{
    /**
     * Display a listing of the resource in the course configuration.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function index(Course $course)
    {
        $this->authorize('viewAny', [File::class, $course]);

        $files = File::where('course_id', $course->id)
            ->orderBy('created_at', 'desc')
            ->paginate(config('const.pagination.per'));

        return view('files.index', [
            'files' => $files,
            'course' => $course,
            'breadcrumbs' => $course
                ->breadcrumbs(true),
        ]);
    }

    /**
     * Display a listing of the resource in the admin panel.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function manage(ManageFiles $request)
    {
        $this->authorize('manage', File::class);

        $files = File::query();

        // If the filter parameter is set, filter the files by status
        $filter = $request->get('filter');
        $files = $this->filter($files, $filter);

        // If the search parameter is set, filter the files by name
        $search = $request->get('search');
        $files = $this->search($files, $search);

        return view('files.manage', [
            'files' => $files
                ->orderBy('created_at', 'desc')
                ->paginate(config('const.pagination.per')),
            'filter' => $filter,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function edit(EditFile $user, int $id)
    {
        $file = File::find($id);

        $this->authorize('update', $file);

        return view('files.edit', [
            'file' => $file,
            'courses' => Course::all(),
            'cards' => $file
                ->cards()
                ->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(UpdateFile $request, int $id)
    {
        $file = File::find($id);

        $this->authorize('update', $file);

        $file->update([
            'name' => $request->get('name'),
        ]);

        // The file can be linked to a(nother) course only
        // if no card(s) are already linked to the file
        if ($file->cards->isEmpty()) {
            $course = $request->get('course') ?
                Course::findOrFail($request->get('course')) : null;

            if ($course) {
                // Determine whether the user can move the file to a specific course
                $this->authorize('move', [
                    File::class,
                    $file,
                    $course,
                ]);

                $course = $course->id;
            }

            $file->update([
                'course_id' => $course,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', trans('messages.file.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function destroy(DestroyFile $request, int $id)
    {
        $file = File::find($id);

        $this->authorize('forceDelete', $file);

        // Delete the record from the database. The binary will
        // be deleted with the FileObserver "deleted" event.
        $file->forceDelete();

        return redirect()
            ->back()
            ->with('success', trans('messages.file.deleted'));
    }

    /**
     * Download the specified file.
     *
     * @throws AuthorizationException
     */
    public function download(DownloadFile $request): BinaryFileResponse
    {
        $fileId = $request->get('file');

        $file = File::find($fileId);

        $this->authorize('download', $file);

        $cardId = $request->get('card');

        // If a card id is given in the request, use the card title.
        // Otherwise, use the file name from the database.
        $fileName = $cardId ? Card::find($cardId)->title : $file->name;

        $fileStorageService = new FileStorageService;

        return response()
            ->download(
                $fileStorageService->fullStandardPath.rawurldecode($file->filename),
                $fileName.'.'.$fileStorageService->getExtension($file->filename),
                ['Cache-Control' => 'no-cache, must-revalidate']
            );
    }

    /**
     * Filter files by status
     */
    private function filter(Builder $files, ?string $filter): Builder
    {
        if (! $filter) {
            return $files;
        }

        return match ($filter) {
            FileStatus::Ready => $files->where('status', FileStatus::Ready),
            FileStatus::Processing => $files->where('status', FileStatus::Processing),
            FileStatus::Transcoding => $files->where('status', FileStatus::Transcoding),
            FileStatus::Failed => $files->where('status', FileStatus::Failed),
            default => $files->select('files.*'),
        };
    }

    /**
     * Filter files by name
     */
    private function search(Builder $files, ?string $search): Builder
    {
        if (! $search) {
            return $files;
        }

        return $files->where(function ($query) use ($search) {
            $query->where('name', 'like', '%'.$search.'%');
        });
    }
}

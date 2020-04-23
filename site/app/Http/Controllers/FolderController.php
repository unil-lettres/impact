<?php

namespace App\Http\Controllers;

use App\Course;
use App\Folder;
use App\Http\Requests\CreateFolder;
use App\Http\Requests\DestroyFolder;
use App\Http\Requests\StoreFolder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return void
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('viewAny', Folder::class);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateFolder $request
     *
     * @return Renderable
     * @throws AuthorizationException
     */
    public function create(CreateFolder $request)
    {
        // Retrieve the course of the folder
        $course = Course::findOrFail($request->input('course'));

        $this->authorize('create', [
            Folder::class,
            $course
        ]);

        return view('folders.create', [
            'course' => $course,
            'folders' => $course
                ->folders()
                ->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreFolder $request
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(StoreFolder $request)
    {
        $course = Course::findOrFail($request->input('course_id'));

        $this->authorize('create', [
            Folder::class,
            $course
        ]);

        // Check also folder select policy if a parent folder is selected
        if($request->input('parent_id')) {
            $this->authorize('select', [
                Folder::class,
                Folder::findOrFail($request->input('parent_id')),
                $course
            ]);
        }

        // Create new folder
        $folder = new Folder($request->all());
        $folder->save();

        return redirect()
            ->route('courses.show', $request->input('course_id'))
            ->with('success', trans('messages.folder.created', ['title' => $folder->title]));
    }

    /**
     * Display the specified resource.
     *
     * @param Folder $folder
     *
     * @return Renderable
     * @throws AuthorizationException
     */
    public function show(Folder $folder)
    {
        $this->authorize('view', $folder);

        return view('folders.show', [
            'folder' => $folder,
            'breadcrumbs' => $folder
                ->breadcrumbs(),
            'cards' => $folder
                ->cards()
                ->get(),
            'folders' => $folder
                ->children()
                ->get()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Folder $folder
     *
     * @return void
     * @throws AuthorizationException
     */
    public function edit(Folder $folder)
    {
        $this->authorize('update', $folder);

        // TODO: add controller logic for edit()
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Folder $folder
     *
     * @return void
     * @throws AuthorizationException
     */
    public function update(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        // TODO: add controller logic for update()
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyFolder $request
     * @param int $id
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy(DestroyFolder $request, int $id)
    {
        $folder = Folder::find($id);
        $course = $folder->course;

        $this->authorize('delete', $folder);

        $folder->delete();

        return redirect()
            ->route('courses.show', $course->id)
            ->with('success', trans('messages.folder.deleted'));
    }
}

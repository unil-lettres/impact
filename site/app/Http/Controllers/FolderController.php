<?php

namespace App\Http\Controllers;

use App\Course;
use App\Folder;
use App\Http\Requests\CreateFolder;
use App\Http\Requests\StoreFolder;
use App\Http\Requests\UpdateFolder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return void
     *
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('viewAny', Folder::class);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function create(CreateFolder $request)
    {
        // Retrieve the course of the folder
        $course = Course::findOrFail($request->input('course'));

        $this->authorize('create', [
            Folder::class,
            $course,
        ]);

        return view('folders.create', [
            'course' => $course,
            'breadcrumbs' => $course
                ->breadcrumbs(true),
            'folders' => $course
                ->folders()
                ->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function store(StoreFolder $request)
    {
        $course = Course::findOrFail($request->input('course_id'));

        $this->authorize('create', [
            Folder::class,
            $course,
        ]);

        // Check also folder select policy if a parent folder is selected
        if ($request->input('parent_id')) {
            $this->authorize('select', [
                Folder::class,
                $course,
                Folder::findOrFail($request->input('parent_id')),
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
     * @return Renderable
     *
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
                ->get(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function edit(Folder $folder)
    {
        $this->authorize('update', $folder);

        // Remove current folder from course folders list
        $folders = $folder->course
            ->folders()
            ->get()
            ->reject(function ($courseFolder) use ($folder) {
                return $courseFolder->id === $folder->id;
            });

        return view('folders.edit', [
            'folder' => $folder,
            'folders' => $folders,
            'parent' => $folder->parent,
            'breadcrumbs' => $folder
                ->breadcrumbs(true),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(UpdateFolder $request, int $id)
    {
        $folder = Folder::find($id);

        $this->authorize('update', $folder);

        // Check also folder select policy if a parent folder is selected
        if ($request->input('parent_id')) {
            $this->authorize('select', [
                Folder::class,
                $folder->course,
                Folder::findOrFail($request->input('parent_id')),
                $folder,
            ]);
        }

        $folder->update([
            'title' => $request->get('title'),
            'parent_id' => $request->get('parent_id'),
        ]);
        $folder->save();

        return redirect()
            ->back()
            ->with('success', trans('messages.folder.updated'));
    }
}

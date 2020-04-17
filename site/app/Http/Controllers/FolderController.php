<?php

namespace App\Http\Controllers;

use App\Course;
use App\Folder;
use App\Http\Requests\CreateFolder;
use App\Http\Requests\StoreFolder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        // TODO: add controller logic for index()
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
        $this->authorize('create', [
            Folder::class,
            Course::findOrFail($request->input('course_id'))
        ]);

        // Create new folder
        $card = new Folder($request->all());
        $card->save();

        // TODO: add translation
        return redirect()->route('courses.show', $request->input('course_id'))
            ->with('success', 'Dossier créé.');
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
            'children' => $folder
                ->children()
                ->get()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Folder $folder
     * @return Response
     */
    public function edit(Folder $folder)
    {
        // TODO: add controller logic for edit()
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Folder $folder
     * @return Response
     */
    public function update(Request $request, Folder $folder)
    {
        // TODO: add controller logic for update()
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Folder $folder
     * @return Response
     */
    public function destroy(Folder $folder)
    {
        // TODO: add controller logic for destroy()
    }
}

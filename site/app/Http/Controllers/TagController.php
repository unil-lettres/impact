<?php

namespace App\Http\Controllers;

use App\Course;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Tag;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTagRequest $request)
    {
        $course = Course::findOrFail($request->course_id);
        $this->authorize('create', [Tag::class, $course]);

        if (Tag::where('name', $request->name)->exists()) {
            return redirect()
                ->back()
                ->with('error', trans('messages.tag.already_exists'));
        }

        $course->tags()->create($request->all());

        return redirect()
            ->back()
            ->with('success', trans('messages.tag.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTagRequest $request, Tag $tag)
    {
        //
    }

    /**
     * Remove (permanently, not soft deleted) the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);

        $tag->forceDelete();

        return redirect()
            ->back()
            ->with('success', trans('messages.tag.deleted'));
    }
}

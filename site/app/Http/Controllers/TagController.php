<?php

namespace App\Http\Controllers;

use App\Course;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Tag;

class TagController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTagRequest $request)
    {
        $course = Course::findOrFail($request->course_id);

        $this->authorize('create', [Tag::class, $course]);

        $course->tags()->create($request->all());

        return redirect()
            ->back()
            ->with('success', trans('messages.tag.created'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $this->authorize('update', $tag);

        $tag->name = $request->name;
        $tag->save();

        return redirect()
            ->back()
            ->with('success', trans('messages.tag.renamed'));
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

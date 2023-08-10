<?php

namespace App\Http\Controllers;

use App\Course;
use App\Http\Requests\IndexTag;
use App\Http\Requests\StoreTag;
use App\Http\Requests\UpdateTag;
use App\Tag;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function index(Course $course, IndexTag $request)
    {
        $this->authorize('viewAny', [Tag::class, $course]);

        $tagOrder = $request->get('tag_order') ?? 'name';
        $tagDirection = $request->get('tag_direction') ?? 'asc';

        $tags = Tag::where('course_id', $course->id)
            ->selectRaw('tags.id, course_id, name, count(card_tag.tag_id) as cards_count')
            ->leftJoin('card_tag', 'tags.id', '=', 'card_tag.tag_id')
            ->groupBy('tags.id', 'course_id', 'name')
            ->orderBy($tagOrder, $tagDirection)
            ->orderBy('name', 'asc')
            ->orderBy('cards_count', 'asc')
            ->get();

        // Get inversed order values for each columns (for url generation).
        $tagColumns = array_merge(
            array_fill_keys(['name', 'cards_count'], 'desc'),
            [
                $tagOrder => ['asc' => 'desc', 'desc' => 'asc'][$tagDirection],
            ],
        );

        if (Auth::user()->admin) {
            $clonableCourses = Course::all();
        } else {
            $clonableCourses = Auth::user()->enrollmentsAsTeacher()->map(
                function ($enrollment) {
                    return $enrollment->course;
                }
            );
        }

        return view('tags.index', [
            'course' => $course,
            'breadcrumbs' => $course
                ->breadcrumbs(true),
            'clonableCourses' => $clonableCourses,
            'tags' => $tags,
            'tagColumns' => $tagColumns,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTag $request)
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
    public function update(Tag $tag, UpdateTag $request)
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

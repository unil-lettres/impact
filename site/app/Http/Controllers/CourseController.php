<?php

namespace App\Http\Controllers;

use App\Course;
use App\Http\Requests\DestroyCourse;
use App\Http\Requests\EnableCourse;
use App\Http\Requests\StoreCourse;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index()
    {
        $courses = Course::orderBy('created_at', 'desc')
            ->get();

        return view('courses.index', [
            'courses' => $courses
        ]);
    }

    /**
     * Display a listing of the resource in the admin panel.
     *
     * @return Renderable
     */
    public function manage()
    {
        $courses = Course::withTrashed()
            ->orderBy('created_at', 'desc')
            ->paginate(config('const.pagination.per'));

        return view('courses.manage', [
            'courses' => $courses
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        return view('courses.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCourse $request
     *
     * @return RedirectResponse
     */
    public function store(StoreCourse $request)
    {
        // Create new course
        $course = new Course($request->all());
        $course->save();

        return redirect()->route('admin.courses.manage')
            ->with('success', trans('messages.course.created', ['name' => $course->name]));
    }

    /**
     * Display the specified resource.
     *
     * @param Course $course
     *
     * @return Renderable
     */
    public function show(Course $course)
    {
        return view('courses.show', [
            'course' => $course,
            'cards' => $course->cards
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Course $course
     *
     * @return Renderable
     */
    public function edit(Course $course)
    {
        return view('courses.edit', [
            'course' => $course
        ]);
    }

    /**
     * Configure the parameters of the specified resource.
     *
     * @param Course $course
     *
     * @return Renderable
     */
    public function configure(Course $course)
    {
        return view('courses.configure', [
            'course' => $course
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Course $course
     *
     * @return Response
     */
    public function update(Request $request, Course $course)
    {
        //
    }

    /**
     * Enable the specified disabled resource.
     *
     * @param EnableCourse $request
     * @param int $id
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function enable(EnableCourse $request, int $id)
    {
        $course = Course::withTrashed()->find($id);

        $course->restore();

        return redirect()->back()
            ->with('success', trans('messages.course.enabled'));
    }

    /**
     * Disable the specified resource (soft delete).
     *
     * @param Course $course
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function disable(Course $course)
    {
        $course->delete();

        return redirect()->back()
            ->with('success', trans('messages.course.disabled'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyCourse $request
     * @param int $id
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function destroy(DestroyCourse $request, int $id)
    {
        $course = Course::withTrashed()->find($id);

        $course->forceDelete();

        return redirect()->back()
            ->with('success', trans('messages.course.deleted'));
    }
}

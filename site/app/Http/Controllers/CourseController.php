<?php

namespace App\Http\Controllers;

use App\Course;
use App\Http\Requests\DestroyCourse;
use App\Http\Requests\EnableCourse;
use App\Http\Requests\StoreCourse;
use Illuminate\Auth\Access\AuthorizationException;
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
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('viewAny', Course::class);

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
     * @throws AuthorizationException
     */
    public function manage()
    {
        $this->authorize('manage', Course::class);

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
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', Course::class);

        return view('courses.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCourse $request
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(StoreCourse $request)
    {
        $this->authorize('create', Course::class);

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
     * @throws AuthorizationException
     */
    public function show(Course $course)
    {
        $this->authorize('view', $course);

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
     * @throws AuthorizationException
     */
    public function edit(Course $course)
    {
        $this->authorize('update', $course);

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
     * @throws AuthorizationException
     */
    public function configure(Course $course)
    {
        $this->authorize('configure', $course);

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
     * @throws AuthorizationException
     */
    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        // TODO: update specified course
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
        $this->authorize('enable', Course::class);

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
        $this->authorize('disable', $course);

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
        $this->authorize('forceDelete', Course::class);

        $course = Course::withTrashed()->find($id);

        $course->forceDelete();

        return redirect()->back()
            ->with('success', trans('messages.course.deleted'));
    }
}

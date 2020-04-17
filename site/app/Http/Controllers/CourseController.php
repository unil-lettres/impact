<?php

namespace App\Http\Controllers;

use App\Course;
use App\Enums\CoursesFilter;
use App\Enums\CourseType;
use App\Http\Requests\DestroyCourse;
use App\Http\Requests\EnableCourse;
use App\Http\Requests\ManageCourses;
use App\Http\Requests\SendCourseDeleteConfirmMail;
use App\Http\Requests\StoreCourse;
use App\Http\Requests\UpdateCourse;
use App\Mail\CourseConfirmDelete;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Exception;
use Illuminate\Support\Facades\Mail;

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
     * @param ManageCourses $request
     *
     * @return Renderable
     * @throws AuthorizationException
     */
    public function manage(ManageCourses $request)
    {
        $this->authorize('manage', Course::class);

        if($request->get('filter')) {
            $courses = $this->filter($request->get('filter'));
        } else {
            $courses = Course::withTrashed();
        }

        return view('courses.manage', [
            'courses' => $courses->orderBy('created_at', 'desc')
                ->paginate(config('const.pagination.per'))
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

        if($request->get('external_id')) {
            // Create new external course
            // TODO: retrieve data from the specified Moodle course if it exists
            // TODO: create student & teacher enrollments
            $course = Course::create([
                'name' => 'Retrieved name from Moodle',
                'description' => 'Retrieved description from Moodle',
                'type' => CourseType::External,
                'external_id' => $request->get('external_id')
            ]);
        } else {
            // Create new local course
            $course = Course::create([
                'name' => $request->input('name'),
                'description' => $request->input('description')
            ]);
        }

        // Save created course
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
            'cards' => $course->rootCards(),
            'folders' => $course->rootFolders()
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
     * @param UpdateCourse $request
     * @param int $id
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(UpdateCourse $request, int $id)
    {
        $course = Course::find($id);

        $this->authorize('update', $course);

        $course->update([
            'name' => $request->get('name'),
            'description' => $request->get('description')
        ]);

        return redirect()->back()
            ->with('success', trans('messages.course.updated'));
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

    /**
     * Send the confirmation mail to delete the resource.
     *
     * @param SendCourseDeleteConfirmMail $request
     * @param int $id
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function mailConfirmDelete(SendCourseDeleteConfirmMail $request, int $id)
    {
        $course = Course::withTrashed()->find($id);

        $this->authorize('mailConfirmDelete', $course);

        // Send the confirmation mail to the teachers of the course
        Mail::to(
            $course->teachers()->map(function ($teacher) {
                return $teacher->email;
            })
        )->send(new CourseConfirmDelete($course));

        return redirect()->back()
            ->with('success', trans('messages.course.delete_confirm.sent'));
    }

    /**
     * Filter courses by parameter
     *
     * @param string $filter
     *
     * @return Course|Builder|\Illuminate\Database\Query\Builder
     */
    private function filter(string $filter) {
        switch ($filter) {
            case CoursesFilter::Disabled:
                return Course::onlyTrashed();
            case CoursesFilter::External:
                return Course::withTrashed()
                    ->where('type', CourseType::External);
            case CoursesFilter::Local:
                return Course::withTrashed()
                    ->where('type', CourseType::Local);
            default:
                return Course::withTrashed();
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Course;
use App\Enrollment;
use App\Enums\CoursesFilter;
use App\Enums\CourseType;
use App\Enums\EnrollmentRole;
use App\Enums\UserType;
use App\Http\Requests\DestroyCourse;
use App\Http\Requests\DisableCourse;
use App\Http\Requests\EnableCourse;
use App\Http\Requests\ManageCourses;
use App\Http\Requests\SendCourseDeleteConfirmMail;
use App\Http\Requests\StoreCourse;
use App\Http\Requests\UpdateConfiguration;
use App\Http\Requests\UpdateCourse;
use App\Mail\CourseConfirmDelete;
use App\Services\MoodleService;
use App\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('viewAny', Course::class);

        $courses = Course::orderBy('created_at', 'desc')
            ->get();

        return view('courses.index', [
            'courses' => $courses,
        ]);
    }

    /**
     * Display a listing of the resource in the admin panel.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function manage(ManageCourses $request)
    {
        $this->authorize('manage', Course::class);

        if ($request->get('filter')) {
            $courses = $this->filter($request->get('filter'));
        } else {
            $courses = Course::withTrashed();
        }

        return view('courses.manage', [
            'courses' => $courses
                ->orderBy('created_at', 'desc')
                ->paginate(config('const.pagination.per')),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     *
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
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function store(StoreCourse $request)
    {
        $this->authorize('create', Course::class);

        $externalId = $request->get('external_id') ?: null;

        if ($externalId) {
            // If external space already exists, return with error
            if (Course::where('external_id', $externalId)->exists()) {
                return redirect()
                    ->route('admin.courses.manage')
                    ->with('error', trans('messages.moodle.course.exists'));
            }

            // Get course data from Moodle
            $courseData = (new MoodleService())
                ->getCourse($externalId);

            // If request fails or no data is found, return with error
            if (! $courseData) {
                return redirect()
                    ->route('admin.courses.manage')
                    ->with('error', trans('messages.moodle.error', ['moodleId' => $externalId]));
            }

            // Create new external course
            $course = Course::create([
                'name' => $courseData['shortname'] ?: 'No name',
                'description' => $courseData['fullname'] ?: null,
                'type' => CourseType::External,
                'external_id' => $externalId,
            ]);

            // Get users data from Moodle, then create new users if
            // needed and finally create enrollments.
            (new MoodleService())
                ->getUsers($externalId)?->each(
                    function ($user) use ($course) {
                        $email = $user['email'] ?: null;
                        $firstname = $user['firstname'] ?: '';
                        $lastname = $user['lastname'] ?: '';
                        $role = $user['role'] ?: null;

                        if ($email && $role) {
                            $user = User::firstOrCreate(
                                ['email' => $email],
                                ['name' => $firstname.' '.$lastname, 'type' => UserType::Aai]
                            );

                            Enrollment::create([
                                'role' => $role,
                                'course_id' => $course->id,
                                'user_id' => $user->id,
                            ]);
                        }
                    }
                );
        } else {
            // Create new local course
            $course = Course::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);
        }

        return redirect()
            ->route('admin.courses.manage')
            ->with('success', trans('messages.course.created', ['name' => $course->name]));
    }

    /**
     * Display the specified resource.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function show(Course $course)
    {
        $this->authorize('view', $course);

        return view('courses.show', [
            'course' => $course,
            'breadcrumbs' => $course
                ->breadcrumbs(),
            'cards' => $course
                ->rootCards(),
            'folders' => $course
                ->rootFolders(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function edit(Course $course)
    {
        $this->authorize('update', $course);

        return view('courses.edit', [
            'course' => $course,
            'users' => User::all(),
            'managerRole' => EnrollmentRole::Manager,
            'usersAsManager' => $course->managers(),
            'memberRole' => EnrollmentRole::Member,
            'usersAsMember' => $course->members(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(UpdateCourse $request, int $id)
    {
        $course = Course::find($id);

        $this->authorize('update', $course);

        $course->update([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
        ]);

        return redirect()
            ->back()
            ->with('success', trans('messages.course.updated'));
    }

    /**
     * Show the form for editing the configuration the specified resource.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function editConfiguration(Course $course)
    {
        $this->authorize('editConfiguration', $course);

        return view('courses.configure', [
            'course' => $course,
            'breadcrumbs' => $course
                ->breadcrumbs(true),
        ]);
    }

    /**
     * Update the specified resource configuration in storage.
     */
    public function updateConfiguration(UpdateConfiguration $request, int $id)
    {
        $course = Course::find($id);

        $this->authorize('updateConfiguration', $course);

        $course->update([
            'transcription' => $request->get('type'),
        ]);

        return redirect()
            ->back()
            ->with('success', trans('messages.course.configuration.updated'));
    }

    /**
     * Enable the specified disabled resource.
     *
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function enable(EnableCourse $request, int $id)
    {
        $this->authorize('enable', Course::class);

        $course = Course::withTrashed()->find($id);

        $course->restore();

        return redirect()
            ->back()
            ->with('success', trans('messages.course.enabled'));
    }

    /**
     * Archive the specified resource.
     *
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function archive(Course $course)
    {
        $this->authorize('archive', $course);

        $course->archive();

        return redirect()
            ->back()
            ->with('success', trans('messages.course.archived'));
    }

    /**
     * Disable the specified resource (soft delete).
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function disable(DisableCourse $request, int $id)
    {
        $course = Course::find($id);

        $this->authorize('disable', $course);

        $course->delete();

        return redirect()
            ->route($request->get('redirect'))
            ->with('success', trans('messages.course.disabled'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(DestroyCourse $request, int $id)
    {
        $this->authorize('forceDelete', Course::class);

        $course = Course::withTrashed()->find($id);

        $course->forceDelete();

        return redirect()
            ->back()
            ->with('success', trans('messages.course.deleted'));
    }

    /**
     * Send the confirmation mail to delete the resource.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function mailConfirmDelete(SendCourseDeleteConfirmMail $request, int $id)
    {
        $course = Course::withTrashed()->find($id);

        $this->authorize('mailConfirmDelete', $course);

        // Send the confirmation mail to the managers of the course
        Mail::to(
            $course->managers(true)->map(function ($manager) {
                return $manager->email;
            })
        )->send(new CourseConfirmDelete($course));

        return redirect()
            ->back()
            ->with('success', trans('messages.course.delete_confirm.sent'));
    }

    /**
     * Filter courses by parameter
     *
     * @return Builder
     */
    private function filter(string $filter)
    {
        $filters = Course::query();

        return match ($filter) {
            CoursesFilter::Disabled => $filters->onlyTrashed(),
            CoursesFilter::External => $filters->withTrashed()
                ->where('type', CourseType::External),
            CoursesFilter::Local => $filters->withTrashed()
                ->where('type', CourseType::Local),
            default => $filters->withTrashed(),
        };
    }
}

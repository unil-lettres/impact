<?php

namespace App\Http\Controllers;

use App\Course;
use App\Enrollment;
use App\Enums\EnrollmentRole;
use App\Http\Requests\DestroyEnrollment;
use App\Http\Requests\FindEnrollment;
use App\Http\Requests\StoreEnrollment;
use App\Http\Requests\UpdateEnrollmentCards;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('viewAny', Enrollment::class);

        return response()->json([
            'enrollments' => auth()->user()->enrollments()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', Enrollment::class);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function store(StoreEnrollment $request)
    {
        $course = Course::findOrFail($request->get('course'));
        $user = User::findOrFail($request->get('user'));

        $this->authorize('create', [
            Enrollment::class,
            $course,
            $user,
        ]);

        Enrollment::create([
            'role' => $request->get('role'),
            'course_id' => $course->id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Display the specified resource.
     *
     *
     * @throws AuthorizationException
     */
    public function show(Enrollment $enrollment)
    {
        $this->authorize('view', Enrollment::class);
    }

    /**
     * Find the specified resource.
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function find(FindEnrollment $request)
    {
        $enrollment = Enrollment::where('course_id', $request->get('course'))
            ->where('user_id', $request->get('user'))
            ->where('role', $request->get('role'))
            ->first();

        $this->authorize('find', $enrollment);

        return response()->json([
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     *
     * @throws AuthorizationException
     */
    public function edit(Enrollment $enrollment)
    {
        $this->authorize('update', $enrollment);
    }

    /**
     * Update the specified resource in storage.
     *
     *
     * @throws AuthorizationException
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        $this->authorize('update', $enrollment);
    }

    /**
     * Update the cards of the resources in storage.
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function cards(UpdateEnrollmentCards $request)
    {
        $courseId = $request->get('course');
        $cardId = $request->get('card');
        $add = collect($request->get('add'));
        $remove = collect($request->get('remove'));
        $userId = $add->first() ?? $remove->first();

        $enrollment = Enrollment::where('course_id', $courseId)
            ->where('user_id', $userId)
            ->where('role', EnrollmentRole::Student)
            ->first();

        $this->authorize('cards', $enrollment);

        return response()->json([
            'success' => $enrollment->updateCard($cardId, $add, $remove),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function destroy(DestroyEnrollment $request, int $id)
    {
        $enrollment = Enrollment::find($id);

        $this->authorize('forceDelete', $enrollment);

        return response()->json([
            'success' => $enrollment->forceDelete() ?? false,
        ]);
    }
}

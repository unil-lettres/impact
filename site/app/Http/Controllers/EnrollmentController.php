<?php

namespace App\Http\Controllers;

use App\Enrollment;
use App\Enums\EnrollmentRole;
use App\Http\Requests\DestroyEnrollment;
use App\Http\Requests\FindEnrollment;
use App\Http\Requests\StoreEnrollment;
use App\Http\Requests\UpdateEnrollmentCards;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
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
     * @param StoreEnrollment $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function store(StoreEnrollment $request)
    {
        $this->authorize('create', Enrollment::class);

        Enrollment::create([
            'role' => $request->get('role'),
            'course_id' => $request->get('course'),
            'user_id' => $request->get('user'),
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param Enrollment $enrollment
     * @throws AuthorizationException
     */
    public function show(Enrollment $enrollment)
    {
        $this->authorize('view', Enrollment::class);
    }

    /**
     * Find the specified resource.
     *
     * @param FindEnrollment $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function find(FindEnrollment $request)
    {
        $this->authorize('find', Enrollment::class);

        $enrollment = Enrollment::where('course_id', $request->get('course'))
            ->where('user_id', $request->get('user'))
            ->where('role', $request->get('role'))
            ->first();

        return response()->json([
            'enrollment' => $enrollment
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Enrollment $enrollment
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
     * @param Request $request
     * @param Enrollment $enrollment
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
     * @param UpdateEnrollmentCards $request
     *
     * @return JsonResponse
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
            'success' => $enrollment->updateCard($cardId, $add, $remove)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyEnrollment $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(DestroyEnrollment $request, int $id)
    {
        $enrollment = Enrollment::find($id);

        $this->authorize('delete', $enrollment);

        return response()->json([
            'success' => $enrollment->delete() ?? false
        ]);
    }
}

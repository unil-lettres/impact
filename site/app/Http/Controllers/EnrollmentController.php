<?php

namespace App\Http\Controllers;

use App\Enrollment;
use Exception;
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
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function store(Request $request)
    {
        // TODO: Add unique validation rule in the request -> https://stackoverflow.com/a/58028505
        // TODO: Add custom validation rule to check if the user of the new enrollment is not an admin

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
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function find(Request $request)
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
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function cards(Request $request)
    {
        $courseId = $request->get('course');
        $cardId = $request->get('card');
        $add = collect($request->get('add'));
        $remove = collect($request->get('remove'));
        $userId = $add->first() ?? $remove->first();

        $enrollment = Enrollment::where('course_id', $courseId)
            ->where('user_id', $userId)
            ->first();

        $this->authorize('cards', $enrollment);

        return response()->json([
            'success' => $enrollment->updateCard($cardId, $add, $remove)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Enrollment $enrollment
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Enrollment $enrollment)
    {
        $this->authorize('delete', $enrollment);

        return response()->json([
            'success' => $enrollment->delete() ?? false
        ]);
    }
}

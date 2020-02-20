<?php

namespace App\Http\Controllers;

use App\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        // TODO: add policy (auth()->user())

        return response()->json([
            'enrollments' => auth()->user()->enrollments()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        // TODO: add policy (auth()->user())
    }

    /**
     * Display the specified resource.
     *
     * @param Enrollment $enrollment
     * @return Response
     */
    public function show(Enrollment $enrollment)
    {
        // TODO: Display the specified resource.
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Enrollment $enrollment
     * @return Response
     */
    public function edit(Enrollment $enrollment)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Enrollment $enrollment
     * @return Response
     */
    public function update(Request $request, Enrollment $enrollment)
    {
    }

    /**
     * Update the cards of the resources identified by a course & by users.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function cards(Request $request)
    {
        // TODO: Update the cards of the resource in storage.

        $courseId = $request->get('course');
        $cardId = $request->get('card');
        $add = collect($request->get('add'));
        $remove = collect($request->get('remove'));
        $userId = $add->first() ?? $remove->first();

        $enrollment = Enrollment::where('course_id', $courseId)
            ->where('user_id', $userId)
            ->first();

        return response()->json([
            'saved' => $enrollment->updateCard($cardId, $add, $remove)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Enrollment $enrollment
     * @return Response
     */
    public function destroy(Enrollment $enrollment)
    {
        // TODO: Remove the specified resource from storage.
    }
}

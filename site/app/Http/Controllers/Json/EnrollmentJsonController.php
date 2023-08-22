<?php

namespace App\Http\Controllers\Json;

use App\Card;
use App\Course;
use App\Enrollment;
use App\Enums\EnrollmentRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyEnrollment;
use App\Http\Requests\StoreEnrollment;
use App\Http\Requests\UpdateEnrollmentCards;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class EnrollmentJsonController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function store(StoreEnrollment $request)
    {
        $course = Course::findOrFail($request->course_id);
        $user = User::findOrFail($request->user_id);

        $this->authorize('create', [
            Enrollment::class,
            $course,
            $user,
        ]);

        Enrollment::create([
            'role' => $request->role,
            'course_id' => $course->id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Add a card to an enrollment.
     *
     * @throws AuthorizationException
     */
    public function attach(UpdateEnrollmentCards $request): JsonResponse
    {
        $card = Card::findOrFail($request->card_id);
        $enrollment = $this->retrieveStudentEnrollment(
            $request->user_id, $card
        );

        $this->authorize('cards', $enrollment);

        $success = $enrollment->addCard($card);

        return response()->json(['success' => $success], 200);
    }

    /**
     * Remove a card from an enrollment.
     *
     * @throws AuthorizationException
     */
    public function detach(UpdateEnrollmentCards $request): JsonResponse
    {
        $card = Card::findOrFail($request->card_id);
        $enrollment = $this->retrieveStudentEnrollment(
            $request->user_id, $card
        );

        $this->authorize('cards', $enrollment);

        $success = $enrollment->removeCard($card);

        return response()->json(['success' => $success], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function destroy(DestroyEnrollment $request)
    {
        $enrollment = Enrollment::where('course_id', $request->course_id)
            ->where('user_id', $request->user_id)
            ->where('role', $request->role)
            ->firstOrFail();

        $this->authorize('forceDelete', $enrollment);

        return response()->json([
            'success' => $enrollment->forceDelete() ?? false,
        ]);
    }

    /**
     * Retrieve the student enrollment associated with the given course and
     * user.
     *
     *
     * @throws ModelNotFoundException
     */
    private function retrieveStudentEnrollment(
        int $userId, Card $card
    ): Enrollment {
        return Enrollment::where('course_id', $card->course->id)
            ->where('user_id', $userId)
            ->where('role', EnrollmentRole::Student)
            ->firstOrFail();
    }
}

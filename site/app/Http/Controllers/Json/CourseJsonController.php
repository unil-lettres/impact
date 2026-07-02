<?php

namespace App\Http\Controllers\Json;

use App\Course;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseJsonController extends Controller
{
    /**
     * Search for local courses matching the given term. Used to lazily
     * populate the course select components on the user edit page.
     *
     * @throws AuthorizationException
     */
    public function search(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $search = $request->input('q');

        $courses = Course::local()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->orderBy('name')
            ->limit(config('const.pagination.per'))
            ->get();

        return response()->json([
            'courses' => $courses,
        ]);
    }
}

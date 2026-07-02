<?php

namespace App\Http\Controllers\Json;

use App\Course;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserJsonController extends Controller
{
    /**
     * Search for users matching the given term.
     *
     * @throws AuthorizationException
     */
    public function search(Request $request, Course $course): JsonResponse
    {
        $this->authorize('viewAny', [User::class, $course]);

        $search = $request->input('q');

        $users = User::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->limit(config('const.pagination.per'))
            ->get();

        return response()->json([
            'users' => $users,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Json;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStatePosition;
use App\State;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class StateJsonController extends Controller
{
    /**
     * Update state position
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function position(UpdateStatePosition $request, int $course_id, int $state_id)
    {
        $state = State::find($state_id);

        $this->authorize('position', $state);

        $newOrder = $request->get('newOrder');

        State::setNewOrder($newOrder, 1);

        return response()->json([
            'success' => $state_id,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Course;
use App\Enums\StateType;
use App\Http\Requests\IndexState;
use App\Http\Requests\StoreState;
use App\Http\Requests\UpdateState;
use App\State;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class StateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param IndexState $request
     * @param int $id
     *
     * @return Renderable
     * @throws AuthorizationException
     */
    public function index(IndexState $request, int $id): Renderable
    {
        $course = Course::find($id);

        $this->authorize('viewAny', [State::class, $course]);

        $states = State::where('course_id', $course->id)
            ->orderBy('position', 'asc')
            ->get();

        $activeState = $request->input('state') ?
            $states->where('id', $request->input('state'))->first() :
            $states->firstWhere('type', StateType::Custom);

        return view('states.index', [
            'states' => $states,
            'activeState' => $activeState,
            'course' => $course,
            'breadcrumbs' => $course
                ->breadcrumbs(true)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreState $request
     * @param int $id
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(StoreState $request, int $id): RedirectResponse
    {
        $course = Course::find($id);

        $this->authorize('create', [State::class, $course]);

        $positionMax = State::where('course_id', $course->id)
            ->where('type', StateType::Custom)
            ->max('position');

        $state = State::create([
            'name' => trans('states.new_state'),
            'position' => $positionMax + 1,
            'course_id' => $course->id
        ]);

        return redirect()
            ->route('courses.configure.states', [$course->id, 'state' => $state->id])
            ->with('success', trans('messages.state.created'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateState $request
     * @param int $course_id
     * @param int $state_id
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(UpdateState $request, int $course_id, int $state_id): RedirectResponse
    {
        $state = State::find($state_id);

        $this->authorize('update', $state);

        $permissions = $state->permissions;
        $permissions['box1'] = $request->get('box1');
        $permissions['box2'] = $request->get('box2');
        $permissions['box3'] = $request->get('box3');
        $permissions['box4'] = $request->get('box4');
        $permissions['box5'] = $request->get('box5');

        $state->update([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'teachers_only' => (bool)$request->get('teachers_only'),
            'permissions' => $permissions
        ]);
        $state->save();

        return redirect()
            ->route('courses.configure.states', [$course_id, 'state' => $state->id])
            ->with('success', trans('messages.state.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param State $state
     * @return Response
     */
    public function destroy(State $state)
    {
        // TODO: add controller logic
    }
}

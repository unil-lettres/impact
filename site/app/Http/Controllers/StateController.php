<?php

namespace App\Http\Controllers;

use App\Course;
use App\Enums\StateType;
use App\Http\Requests\IndexState;
use App\Http\Requests\StoreState;
use App\State;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function store(StoreState $request, int $id)
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
     * Display the specified resource.
     *
     * @param State $state
     * @return Response
     */
    public function show(State $state)
    {
        // TODO: add controller logic
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param State $state
     * @return Response
     */
    public function edit(State $state)
    {
        // TODO: add controller logic
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param State $state
     * @return Response
     */
    public function update(Request $request, State $state)
    {
        // TODO: add controller logic
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

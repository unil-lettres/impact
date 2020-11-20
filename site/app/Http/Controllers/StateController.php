<?php

namespace App\Http\Controllers;

use App\Course;
use App\State;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Course $course
     *
     * @return Renderable
     * @throws AuthorizationException
     */
    public function index(Course $course)
    {
        $this->authorize('viewAny', [State::class, $course]);

        $states = State::where('course_id', $course->id)
            ->orderBy('created_at', 'desc');

        return view('states.index', [
            'states' => $states,
            'course' => $course,
            'breadcrumbs' => $course
                ->breadcrumbs(true)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        // TODO: add controller logic
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        // TODO: add controller logic
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

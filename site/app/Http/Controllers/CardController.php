<?php

namespace App\Http\Controllers;

use App\Card;
use App\Course;
use App\Folder;
use App\Http\Requests\CreateCard;
use App\Http\Requests\DestroyCard;
use App\Http\Requests\StoreCard;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return void
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('viewAny', Card::class);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateCard $request
     *
     * @return RedirectResponse|Renderable
     * @throws AuthorizationException
     */
    public function create(CreateCard $request)
    {
        // Retrieve the course of the card
        $course = Course::findOrFail($request->input('course'));

        $this->authorize('create', [
            Card::class,
            $course
        ]);

        return view('cards.create', [
            'course' => $course,
            'breadcrumbs' => $course
                ->breadcrumbs(true),
            'folders' => $course
                ->folders()
                ->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCard $request
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(StoreCard $request)
    {
        $course = Course::findOrFail($request->input('course_id'));

        $this->authorize('create', [
            Card::class,
            $course
        ]);

        // Check also folder select policy if a folder is selected
        if($request->input('folder_id')) {
            $this->authorize('select', [
                Folder::class,
                $course,
                Folder::findOrFail($request->input('folder_id'))
            ]);
        }

        // Create new card
        $card = new Card($request->all());
        $card->save();

        return redirect()
            ->route('courses.show', $request->input('course_id'))
            ->with('success', trans('messages.card.created', ['title' => $card->title]));
    }

    /**
     * Display the specified resource.
     *
     * @param Card $card
     *
     * @return Renderable
     * @throws AuthorizationException
     */
    public function show(Card $card)
    {
        $this->authorize('view', $card);

        return view('cards.show', [
            'card' => $card,
            'breadcrumbs' => $card
                ->breadcrumbs(),
            'course' => $card->course
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Card $card
     *
     * @return Renderable
     * @throws AuthorizationException
     */
    public function edit(Card $card)
    {
        $this->authorize('update', $card);

        return view('cards.edit', [
            'card' => $card,
            'breadcrumbs' => $card
                ->breadcrumbs(true),
            'editors' => $card
                ->editors(),
            'students' => $card->course
                ->students()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Card $card
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, Card $card)
    {
        $this->authorize('update', $card);

        return redirect()
            ->route('cards.show', $card->id)
            ->with('success', trans('messages.card.configuration.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyCard $request
     * @param int $id
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy(DestroyCard $request, int $id)
    {
        $card = Card::find($id);
        $course = $card->course;

        $this->authorize('delete', $card);

        $card->delete();

        return redirect()
            ->route('courses.show', $course->id)
            ->with('success', trans('messages.card.deleted'));
    }

    /**
     * Unlink file from the specified resource.
     *
     * @param Card $card
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function unlinkFile(Card $card)
    {
        $this->authorize('unlinkFile', $card);

        $card->update([
            'file_id' => null
        ]);
        $card->save();

        return redirect()
            ->back()
            ->with('success', trans('messages.card.unlinked'));
    }
}

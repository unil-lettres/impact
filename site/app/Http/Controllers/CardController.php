<?php

namespace App\Http\Controllers;

use App\Card;
use App\Course;
use App\Http\Requests\CreateCard;
use App\Http\Requests\StoreCard;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index()
    {
        // TODO: add authorize

        $cards = Card::orderBy('created_at', 'desc')
            ->paginate(config('const.pagination.per'));

        return view('cards.index', [
            'cards' => $cards
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateCard $request
^     *
     * @return RedirectResponse|Renderable
     */
    public function create(CreateCard $request)
    {
        // TODO: add authorize

        // Retrieve the course of the card
        $courseId = $request->input('course');
        try {
            $course = Course::findOrFail($courseId);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return redirect()->back();
        }

        return view('cards.create', [
            'course' => $course
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCard $request
     *
     * @return RedirectResponse
     */
    public function store(StoreCard $request)
    {
        // TODO: add authorize

        // Create new course
        $card = new Card($request->all());
        $card->save();

        return redirect()->route('courses.show', $request->input('course_id'))
            ->with('success', trans('messages.card.created', ['title' => $card->title]));
    }

    /**
     * Display the specified resource.
     *
     * @param Card $card
     *
     * @return Renderable
     */
    public function show(Card $card)
    {
        // TODO: add authorize

        return view('cards.show', [
            'card' => $card,
            'course' => $card->course
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Card $card
     *
     * @return Response
     */
    public function edit(Card $card)
    {
        // TODO: Show the form for editing the specified resource.
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Card $card
     *
     * @return Response
     */
    public function update(Request $request, Card $card)
    {
        // TODO: Update the specified resource in storage.
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Card $card
     *
     * @return Response
     */
    public function destroy(Card $card)
    {
        // TODO: Remove the specified resource from storage.
    }
}

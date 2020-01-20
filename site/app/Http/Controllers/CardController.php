<?php

namespace App\Http\Controllers;

use App\Card;
use App\Course;
use App\Http\Requests\CreateCard;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
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
     * @return Response
     */
    public function create(CreateCard $request)
    {
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
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Card $card
     *
     * @return Response
     */
    public function show(Card $card)
    {
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
        //
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
        //
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
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Card;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
     * @return Response
     */
    public function create()
    {
        return view('cards.create');
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
            'card' => $card
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

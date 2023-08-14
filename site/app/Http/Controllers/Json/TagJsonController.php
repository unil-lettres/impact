<?php

namespace App\Http\Controllers\Json;

use App\Card;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTag;
use App\Tag;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class TagJsonController extends Controller
{
    /**
     * Create and attach a tag to a card.
     *
     *
     * @throws AuthorizationException
     */
    public function create(CreateTag $request): JsonResponse
    {
        $card = Card::findOrFail($request->card_id);

        $this->authorize('update', $card);

        $tag = Tag::create([
            'course_id' => $card->course->id,
            'card_id' => $card->id,
            'name' => $request->name,
        ]);
        $card->tags()->attach($tag);

        return response()->json(['tag_id' => $tag->id], 200);
    }

    /**
     * Attach a tag to a card.
     *
     *
     * @throws AuthorizationException
     */
    public function attach(Tag $tag, Card $card): JsonResponse
    {
        $this->authorize('update', $card);

        $card->tags()->attach($tag);

        return response()->json(['success' => true], 200);
    }

    /**
     * Detach a tag from a card.
     *
     *
     * @throws AuthorizationException
     */
    public function detach(Tag $tag, Card $card): JsonResponse
    {
        $this->authorize('update', $card);

        $card->tags()->detach($tag);

        return response()->json(['success' => true], 200);
    }
}

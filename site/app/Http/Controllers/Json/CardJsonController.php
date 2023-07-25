<?php

namespace App\Http\Controllers\Json;

use App\Card;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTagInCard;
use App\Http\Requests\UpdateCardEditor;
use App\Http\Requests\UpdateCardTranscription;
use App\Tag;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class CardJsonController extends Controller
{
    /**
     * Update the editor html from the specified resource.
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function editor(UpdateCardEditor $request, int $id)
    {
        $card = Card::find($id);
        $box = $request->get('box');

        $this->authorize('box', [
            Card::class,
            $card,
            $box,
        ]);

        $html = $request->get('html');

        $card->update([
            $box => $html,
        ]);
        $card->save();

        return response()->json([
            'success' => $id,
        ], 200);
    }

    /**
     * Update the transcription from the specified resource.
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function transcription(UpdateCardTranscription $request, int $id)
    {
        $card = Card::find($id);
        $box = $request->get('box');

        $this->authorize('box', [
            Card::class,
            $card,
            $box,
        ]);

        $box2 = $card->box2 ?? json_decode(Card::TRANSCRIPTION, true);
        $box2['data'] = $request->get('transcription') ? $request->get('transcription') : [];

        $card->update([
            $box => $box2,
        ]);
        $card->save();

        return response()->json([
            'success' => $id,
        ], 200);
    }

    /**
     * Create and link a tag to a card.
     *
     *
     * @throws AuthorizationException
     */
    public function createTag(Card $card, CreateTagInCard $request)
    {
        $this->authorize('update', $card);

        $tag = $card->course->tags()->create($request->all());
        $card->tags()->attach($tag);

        return response()->json(['tag_id' => $tag->id], 200);
    }

    /**
     * Link a tag to a card.
     *
     *
     * @throws AuthorizationException
     */
    public function linkTag(Card $card, Tag $tag)
    {
        $this->authorize('update', $card);

        $card->tags()->attach($tag);

        return response()->json(['success' => true], 200);
    }

    /**
     * Unlink a tag from a card.
     *
     *
     * @throws AuthorizationException
     */
    public function unlinkTag(Card $card, Tag $tag)
    {
        $this->authorize('update', $card);

        $card->tags()->detach($tag);

        return response()->json(['success' => true], 200);
    }
}

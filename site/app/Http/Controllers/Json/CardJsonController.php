<?php

namespace App\Http\Controllers\Json;

use App\Card;
use App\Enums\CardBox;
use App\Enums\TranscriptionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCardEditor;
use App\Http\Requests\UpdateCardTranscription;
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

        // Box2 data initialization based on the transcription type.
        // If the box is not box2, the data is stored as is.
        $html = match ($box) {
            CardBox::Box2 => $this->initBox2Data(
                $card,
                TranscriptionType::Text,
                $request->get('html')
            ),
            default => $request->get('html'),
        };

        $card->update([
            $box => $html,
        ]);

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

        $card->update([
            $box => $this->initBox2Data(
                $card,
                TranscriptionType::Icor,
                $request->get('transcription')
            ),
        ]);

        return response()->json([
            'success' => $id,
        ], 200);
    }

    /**
     * Return a list of tags to display for the user.
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function tagsInline(Card $card)
    {
        $this->authorize('view', $card);

        return response()->json([
            'value' => $card->tags->isEmpty() ? '-' : $card->tags->implode('name', ', '),
        ], 200);
    }

    /**
     * Box2 data initialization based on the transcription type.
     *
     * @param  string  $type  (App\Enums\TranscriptionType)
     */
    private function initBox2Data(Card $card, string $type, mixed $content): array
    {
        $box2 = $card->box2 ?? json_decode(Card::TRANSCRIPTION, true);

        $box2[$type] = match ($type) {
            TranscriptionType::Icor => $content ?? [],
            default => $content ?? null,
        };

        return $box2;
    }
}

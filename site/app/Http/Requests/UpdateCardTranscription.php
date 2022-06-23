<?php

namespace App\Http\Requests;

use App\Enums\CardBox;
use App\Rules\Transcription;
use Illuminate\Validation\Rule;

class UpdateCardTranscription extends AbstractRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'card' => 'required|integer|exists:cards,id',
            'transcription' => [
                'nullable',
                'array',
                new Transcription,
            ],
            'box' => [
                'required',
                'string',
                Rule::in(
                    [CardBox::Box2]
                ),
            ],
        ];
    }
}

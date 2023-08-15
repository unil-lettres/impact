<?php

namespace App\Http\Requests;

use App\Enums\CardBox;
use Illuminate\Validation\Rule;

class UpdateCardEditor extends AbstractRequest
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
            'box' => [
                'required',
                'string',
                Rule::in(
                    [CardBox::Box2, CardBox::Box3, CardBox::Box4]
                ),
            ],
        ];
    }
}

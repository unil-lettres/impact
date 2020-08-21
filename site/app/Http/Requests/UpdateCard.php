<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateCard extends AbstractRequest
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
            'box1-hidden' => [
                'sometimes',
                Rule::in([ 'on' ])
            ],
            'box1-file' => 'integer|exists:files,id|nullable',
            'box1-link' => 'url|nullable',
            'box2-hidden' => [
                'sometimes',
                Rule::in([ 'on' ])
            ],
            'box2-sync' => [
                'sometimes',
                Rule::in([ 'on' ])
            ],
            'box3-hidden' => [
                'sometimes',
                Rule::in([ 'on' ])
            ],
            'box3-title' => 'required|string|max:255',
            'box4-hidden' => [
                'sometimes',
                Rule::in([ 'on' ])
            ],
            'box4-title' => 'required|string|max:255',
            'box5-hidden' => [
                'sometimes',
                Rule::in([ 'on' ])
            ],
            'emails' => [
                'sometimes',
                Rule::in([ 'on' ])
            ]
        ];
    }
}

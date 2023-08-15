<?php

namespace App\Http\Requests;

use App\Enums\TranscriptionType;
use Illuminate\Validation\Rule;

class UpdateConfiguration extends AbstractRequest
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
            'course' => 'required|integer|exists:courses,id',
            'type' => [
                'required',
                'string',
                Rule::in(
                    [TranscriptionType::Icor, TranscriptionType::Text]
                ),
            ],
        ];
    }
}

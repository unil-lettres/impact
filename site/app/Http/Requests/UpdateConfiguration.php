<?php

namespace App\Http\Requests;

use App\Enums\TranscriptionType;
use Illuminate\Validation\Rule;

class UpdateConfiguration extends AbstractRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
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

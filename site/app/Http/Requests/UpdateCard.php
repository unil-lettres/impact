<?php

namespace App\Http\Requests;

use App\Rules\StateAvailability;
use Illuminate\Validation\Rule;

class UpdateCard extends AbstractRequest
{
    const TITLE_VALIDATION = 'required|string|max:255';

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
            'card' => 'required|integer|exists:cards,id',
            'title' => static::TITLE_VALIDATION,
            'emails' => [
                'sometimes',
                Rule::in(['on']),
            ],
            'presentation_date' => [
                'nullable',
                'date_format:d/m/Y',
            ],
            'state' => [
                'required',
                'integer',
                'exists:states,id',
                new StateAvailability,
            ],
            'box1-hidden' => [
                'sometimes',
                Rule::in(['on']),
            ],
            'box1-file' => 'integer|exists:files,id|nullable',
            'box1-link' => 'url|nullable',
            'box1-start' => 'integer|nullable',
            'box1-end' => 'integer|nullable',
            'box2-hidden' => [
                'sometimes',
                Rule::in(['on']),
            ],
            'box2-sync' => [
                'sometimes',
                Rule::in(['on']),
            ],
            'box3-hidden' => [
                'sometimes',
                Rule::in(['on']),
            ],
            'box3-title' => 'sometimes|string|max:255',
            'box4-hidden' => [
                'sometimes',
                Rule::in(['on']),
            ],
            'box4-title' => 'sometimes|string|max:255',
            'box5-hidden' => [
                'sometimes',
                Rule::in(['on']),
            ],
        ];
    }
}

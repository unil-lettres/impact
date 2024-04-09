<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class IndexTag extends AbstractRequest
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
            'tag_order' => [
                'string',
                Rule::in(
                    ['name', 'cards_count']
                ),
            ],
            'tag_direction' => [
                'string',
                Rule::in(['asc', 'desc']),
            ],
        ];
    }
}

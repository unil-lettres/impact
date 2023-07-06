<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ConfigureCourseRequest extends AbstractRequest
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

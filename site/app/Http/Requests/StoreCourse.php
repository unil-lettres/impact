<?php

namespace App\Http\Requests;

class StoreCourse extends AbstractRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'string|required_if:external_id,null|max:255',
            'description' => 'string|max:3000|nullable',
            'external_id' => 'integer|min:0|nullable',
        ];
    }
}

<?php

namespace App\Http\Requests;

class UpdateCourse extends AbstractRequest
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
            'course' => 'required|integer|exists:courses,id',
            'name' => 'string|required|max:255',
            'description' => 'string|max:3000|nullable',
        ];
    }
}

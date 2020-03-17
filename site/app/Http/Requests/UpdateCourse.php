<?php

namespace App\Http\Requests;

class UpdateCourse extends AbstractRequest
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
            'name' => 'string|required|max:255',
            'description' => 'string|max:3000|nullable'
        ];
    }
}

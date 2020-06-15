<?php

namespace App\Http\Requests;

class UpdateFile extends AbstractRequest
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
            'file' => 'required|integer|exists:files,id',
            'name' => 'required|string|max:255',
            'course_id' => 'integer|exists:courses,id|nullable',
        ];
    }
}

<?php

namespace App\Http\Requests;

class CloneTags extends AbstractRequest
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
            'course_id_from' => 'required|integer|exists:courses,id',
            'course_id_to' => 'required|integer|exists:courses,id',
        ];
    }
}

<?php

namespace App\Http\Requests;

class StoreCard extends AbstractRequest
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
            'title' => 'required|string|max:255',
            'course_id' => 'required|integer|exists:courses,id',
            'state_id' => 'required|integer|exists:states,id',
            'folder_id' => 'integer|exists:folders,id|nullable',
        ];
    }
}

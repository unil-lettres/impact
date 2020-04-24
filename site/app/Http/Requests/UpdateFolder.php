<?php

namespace App\Http\Requests;

class UpdateFolder extends AbstractRequest
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
            'folder' => 'required|integer|exists:folders,id',
            'title' => 'required|string|max:200',
            'parent_id' => 'integer|exists:folders,id|nullable',
        ];
    }
}

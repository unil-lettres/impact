<?php

namespace App\Http\Requests;

class UpdateStatePosition extends AbstractRequest
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
            'state' => 'required|integer|exists:states,id',
            'newOrder' => 'array|required',
        ];
    }
}

<?php

namespace App\Http\Requests;

class StoreInvitation extends AbstractRequest
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
            'email' => 'required|email|unique:invitations|unique:users',
            'course' => 'required|integer|exists:courses,id',
        ];
    }
}

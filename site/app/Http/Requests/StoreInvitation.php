<?php

namespace App\Http\Requests;

use App\Rules\InvitationUniqueness;

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
            'email' => [
                'required',
                'email',
                new InvitationUniqueness,
            ],
            'course' => 'required|integer|exists:courses,id',
        ];
    }
}

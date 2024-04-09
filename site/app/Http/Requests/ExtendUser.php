<?php

namespace App\Http\Requests;

use App\Rules\RefuteAdmins;

class ExtendUser extends AbstractRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user' => [
                'required',
                'integer',
                'exists:users,id',
                new RefuteAdmins,
            ],
        ];
    }
}

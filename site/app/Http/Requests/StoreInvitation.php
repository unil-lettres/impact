<?php

namespace App\Http\Requests;

use App\Rules\InvitationUniqueness;

class StoreInvitation extends AbstractRequest
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
            'email' => [
                'required',
                'email',
                new InvitationUniqueness,
            ],
            'course' => 'required|integer|exists:courses,id',
        ];
    }
}

<?php

namespace App\Http\Requests;

class UpdateEnrollmentCards extends AbstractRequest
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
            'card_id' => 'required|integer|exists:cards,id',
            'user_id' => 'required|integer|exists:users,id',
        ];
    }
}

<?php

namespace App\Http\Requests;

class DestroyCard extends AbstractRequest
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
            'card' => 'required|integer|exists:cards,id',
        ];
    }
}

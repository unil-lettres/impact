<?php

namespace App\Http\Requests;

class CreateTag extends StoreTag
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'card_id' => 'required|integer|exists:cards,id',
            'name' => parent::rules()['name'],
        ];
    }
}

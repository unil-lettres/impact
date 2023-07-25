<?php

namespace App\Http\Requests;

use App\Http\Requests\StoreTagRequest;

class UpdateTagRequest extends StoreTagRequest
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
            'name' => parent::rules()['name'],
        ];
    }
}

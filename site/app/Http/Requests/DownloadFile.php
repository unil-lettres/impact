<?php

namespace App\Http\Requests;

class DownloadFile extends AbstractRequest
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
            'file' => 'required|integer|exists:files,id',
            'card' => 'sometimes|integer|exists:cards,id',
        ];
    }
}

<?php

namespace App\Http\Requests;

class StoreUpload extends AbstractRequest
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
            'course_id' => 'nullable|integer|exists:courses,id',
            'card_id' => 'nullable|integer|exists:cards,id',
            'attachment' => 'required|boolean',
        ];
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'course_id' => json_decode($this->course_id),
            'card_id' => json_decode($this->card_id),
            'attachment' => json_decode($this->attachment),
        ]);
    }
}

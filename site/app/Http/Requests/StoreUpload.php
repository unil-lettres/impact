<?php

namespace App\Http\Requests;

class StoreUpload extends AbstractRequest
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
            'course_id' => 'nullable|integer|exists:courses,id',
            'card_id' => 'integer|required_if:attachment,true|exists:cards,id',
            'attachment' => 'required|boolean',
        ];
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'attachment' => $this->toBoolean($this->attachment),
        ]);
    }

    /**
     * Convert to boolean
     */
    private function toBoolean(string $boolean): bool
    {
        return filter_var($boolean, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}

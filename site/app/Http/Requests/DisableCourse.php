<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class DisableCourse extends AbstractRequest
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
            'course' => 'required|integer|exists:courses,id',
            'redirect' => [
                'required',
                'string',
                Rule::in(
                    ['home', 'admin.courses.manage']
                ),
            ],
        ];
    }
}

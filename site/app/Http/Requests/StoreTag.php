<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTag extends FormRequest
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
            'course_id' => 'required|integer|exists:courses,id',
            'name' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('tags', 'name')->where(
                    fn (Builder $query) => $query->where(
                        'course_id', $this->input('course_id')
                    )
                ),
            ],
        ];
    }
}

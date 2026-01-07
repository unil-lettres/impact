<?php

namespace App\Http\Requests;

use App\Enums\CoursesFilter;
use Illuminate\Validation\Rule;

class IndexCourses extends AbstractRequest
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
            'filter' => [
                'string',
                Rule::in(
                    [CoursesFilter::Own]
                ),
            ],
            'sort' => [
                'string',
                Rule::in(['created_at', 'name']),
            ],
            'direction' => [
                'string',
                Rule::in(['asc', 'desc']),
            ],
        ];
    }
}

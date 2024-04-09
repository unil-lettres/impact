<?php

namespace App\Http\Requests;

use App\Enums\CoursesFilter;
use Illuminate\Validation\Rule;

class IndexCourses extends AbstractRequest
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
            'filter' => [
                'string',
                Rule::in(
                    [CoursesFilter::Own]
                ),
            ],
        ];
    }
}

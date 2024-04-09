<?php

namespace App\Http\Requests;

use App\Enums\CoursesFilter;
use Illuminate\Validation\Rule;

class ManageCourses extends AbstractRequest
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
            'search' => ['string', 'max:255'],
            'filter' => [
                'string',
                Rule::in(
                    [CoursesFilter::Disabled, CoursesFilter::External, CoursesFilter::Local]
                ),
            ],
        ];
    }
}

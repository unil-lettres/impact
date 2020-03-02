<?php

namespace App\Http\Requests;

use App\Enums\EnrollmentRole;
use Illuminate\Validation\Rule;

class FindEnrollment extends AbstractRequest
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
            'role' => [
                'required',
                'string',
                Rule::in(
                    [ EnrollmentRole::Student, EnrollmentRole::Teacher ]
                )
            ],
            'user' => 'required|integer|exists:users,id',
            'course' => 'required|integer|exists:courses,id'
        ];
    }
}

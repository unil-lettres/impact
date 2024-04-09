<?php

namespace App\Http\Requests;

use App\Enums\EnrollmentRole;
use App\Rules\EnrollmentUniqueness;
use Illuminate\Validation\Rule;

class StoreEnrollment extends AbstractRequest
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
            'role' => [
                'required',
                'string',
                Rule::in(
                    [EnrollmentRole::Member, EnrollmentRole::Manager]
                ),
                new EnrollmentUniqueness,
            ],
            'user_id' => 'required|integer|exists:users,id',
            'course_id' => 'required|integer|exists:courses,id',
        ];
    }
}

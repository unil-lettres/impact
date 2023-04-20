<?php

namespace App\Rules;

use App\Enrollment;
use Illuminate\Contracts\Validation\Rule;

class EnrollmentUniqueness implements Rule
{
    private $course_id;

    private $user_id;

    /**
     * Create a new rule instance.
     */
    public function __construct(int $course, int $user)
    {
        $this->course_id = $course;
        $this->user_id = $user;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Check if an enrollment with the same course_id & user_id already exists
        $enrollment = Enrollment::where('role', $value)
            ->where('course_id', $this->course_id)
            ->where('user_id', $this->user_id)
            ->first();

        // If the enrollment already exists the validation rule should return false,
        // if a similar enrollment cannot be found the validation rule should return true.
        return $enrollment ? false : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This enrollment already exists.';
    }
}

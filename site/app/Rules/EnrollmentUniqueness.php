<?php

namespace App\Rules;

use App\Enrollment;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class EnrollmentUniqueness implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     */
    protected array $data = [];

    /**
     * Set the data under validation.
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if an enrollment with the same course_id & user_id already exists
        $enrollment = Enrollment::where('role', $value)
            ->where('course_id', $this->data['course_id'])
            ->where('user_id', $this->data['user_id'])
            ->first();

        // If the enrollment already exists the validation rule should fail,
        // if a similar enrollment cannot be found the validation rule should pass.
        if ($enrollment) {
            $fail('This enrollment already exists.');
        }
    }
}

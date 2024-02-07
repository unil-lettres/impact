<?php

namespace App\Rules;

use App\User;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Translation\PotentiallyTranslatedString;

class Holders implements DataAwareRule, ValidationRule
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
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // $value is an array of user ids and names
        foreach ($value as $id => $name) {
            $user = User::find($id);

            if (! $user) {
                $fail('Cannot find user "'.$name.'"');
            }

            if (! Arr::hasAny($this->data, 'course.id')) {
                $fail('Course not available');
            }

            if (! $user->enrollments()->where('course_id', $this->data['course']['id'])->exists()) {
                $fail('User "'.$name.'" is not enrolled in the course');
            }
        }
    }
}

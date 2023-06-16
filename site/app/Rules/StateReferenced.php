<?php

namespace App\Rules;

use App\State;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StateReferenced implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $state = State::find($value);

        if (! $state->cards->isEmpty()) {
            $fail('Cannot delete a state already referenced by a card.');
        }
    }
}

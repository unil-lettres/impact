<?php

namespace App\Rules;

use App\Card;
use App\Enums\StateType;
use App\State;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class StateAvailability implements DataAwareRule, ValidationRule
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
        $card = Card::find($this->data['card']);
        $state = State::find($value);

        if (! $card || ! $state) {
            $fail('Operation not permitted.');
        }

        if (! Auth::user()->isTeacher($card->course)) {
            if ($state->type === StateType::Archived || $state->managers_only) {
                $fail('The selected state is not available to the current user.');
            }
        }
    }
}

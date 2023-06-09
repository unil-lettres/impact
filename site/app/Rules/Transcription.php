<?php

namespace App\Rules;

use Assert\Assert;
use Assert\InvalidArgumentException;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Transcription implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            // The transcription should be a collection of arrays
            // including specific keys
            Assert::thatAll($value)
                ->isArray()
                ->notEmpty()
                ->keyExists('number')
                ->keyExists('speaker')
                ->keyExists('speech');
        } catch (InvalidArgumentException $e) {
            $fail('The transcription is not valid.');
        }
    }
}

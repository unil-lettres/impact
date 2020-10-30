<?php

namespace App\Rules;

use Assert\Assert;
use Assert\InvalidArgumentException;
use Illuminate\Contracts\Validation\Rule;

class Transcription implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The transcription is not valid.';
    }
}

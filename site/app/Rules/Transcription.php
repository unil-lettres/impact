<?php

namespace App\Rules;

use App\Card;
use Assert\Assert;
use Assert\InvalidArgumentException;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class Transcription implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

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

            Assert::thatAll(array_map(fn ($row) => $row['speaker'] ?? '', $value))
                ->maxLength(3);

            $cardId = $this->data['card'];
            $maxCharacters = Card::findOrFail($cardId)->getMaxCharactersByLine();
            if ($maxCharacters) {
                foreach (array_map(fn ($row) => $row['speech'] ?? '', $value) as $speech) {
                    Assert::that($speech)->maxLength($maxCharacters + 1);

                    // When the length of a line is maxCharacters + 1, the last
                    // characters must be a whitespace.
                    assert(false
                        || strlen($speech) <= $maxCharacters
                        || preg_match('/\s/', $speech[$maxCharacters])
                    );
                }
            }

        } catch (InvalidArgumentException $e) {
            $fail('The transcription is not valid.');
        }
    }

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}

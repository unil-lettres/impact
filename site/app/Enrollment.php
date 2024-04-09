<?php

namespace App;

use App\Scopes\ValidityScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'role', 'course_id', 'user_id', 'cards',
    ];

    protected function casts(): array
    {
        return [
            'cards' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new ValidityScope());
    }

    /**
     * Get the course of this enrollment.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the user of this enrollment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the enrollment has a specific card.
     */
    public function hasCard(Card $card): bool
    {
        return $this->cards && in_array($card->id, $this->cards);
    }

    /**
     * Add a card to the enrollment if the card doesn't exist in the enrollment cards.
     * Return true if a card was added, false otherwise.
     */
    public function addCard(Card $card): bool
    {
        if (! $this->hasCard($card)) {
            $cards = collect($this->cards);

            $cards = $cards->push($card->id);

            $this->update([
                'cards' => $cards->toArray(),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Remove a card from the enrollment if the card exist in the enrollment cards.
     * Return true if a card was removed, false otherwise.
     */
    public function removeCard(Card $card): bool
    {
        if ($this->hasCard($card)) {
            $cards = collect($this->cards);

            $cards = $cards->reject(function ($cardId) use ($card) {
                return $cardId == $card->id;
            });

            // We need to call values() to reset the keys of the collection.
            // Otherwise if we remove the first card of the collection and
            // losing the element at index 0, Eloquent (or something in-between)
            // will not write the values as we would in database.
            // Values 'array( 1 => 2, 2 => 5)' will be writed in db as '{"1":2,"2":5}',
            // but 'array( 0 => 2, 1 => 5 )' as '[2,5]'.
            $this->update([
                'cards' => $cards->values()->toArray(),
            ]);

            return true;
        }

        return false;
    }
}

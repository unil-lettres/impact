<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\Log;

class Enrollment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'role', 'course_id', 'user_id', 'cards',
    ];

    protected $casts = [
        'cards' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the course of this enrollment.
     */
    public function course()
    {
        return $this->hasOne('App\Course', 'id', 'course_id');
    }

    /**
     * Get the user of this enrollment.
     */
    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    /**
     * Check if the enrollment has a specific card.
     *
     * @return bool
     */
    public function hasCard(Card $card)
    {
        return $this->cards ? in_array($card->id, $this->cards) : false;
    }

    /**
     * Add a card to the enrollment if the card doesn't exist in the enrollment cards.
     * Return true if a card was added, false otherwise.
     *
     * @return bool
     */
    public function addCard(Card $card)
    {
        if (! $this->hasCard($card)) {
            $cards = collect($this->cards);

            $cards = $cards->push($card->id);

            $this->update([
                'cards' => $cards->toArray(),
            ]);
            $this->save();

            return true;
        }

        return false;
    }

    /**
     * Remove a card from the enrollment if the card exist in the enrollment cards.
     * Return true if a card was removed, false otherwise.
     *
     * @return bool
     */
    public function removeCard(Card $card)
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
            $this->save();

            return true;
        }

        return false;
    }

    /**
     * Add card to the enrollment if provided.
     * Remove card from the enrollment if provided.
     *
     * @return bool
     */
    public function updateCard(int $cardId, Collection $add, Collection $remove)
    {
        $card = Card::findOrFail($cardId);

        if ($add->isNotEmpty()) {
            return $this->addCard($card);
        }

        if ($remove->isNotEmpty()) {
            return $this->removeCard($card);
        }

        return false;
    }
}

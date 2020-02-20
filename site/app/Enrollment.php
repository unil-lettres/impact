<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'cards',
    ];

    protected $casts = [
        'cards' => 'array',
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
     * @param Card $card
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
     * @param Card $card
     *
     * @return bool
     */
    public function addCard(Card $card)
    {
        if (!$this->hasCard($card)) {
            $cards = collect($this->cards);

            $cards = $cards->push($card->id);

            $this->update([
                'cards' => $cards->toArray()
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
     * @param Card $card
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

            $this->update([
                'cards' => $cards->toArray()
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
     * @param int $cardId
     * @param Collection $add
     * @param Collection $remove
     *
     * @return bool
     */
    public function updateCard(int $cardId, Collection $add, Collection $remove)
    {
        $card = Card::findOrFail($cardId);

        if($add->isNotEmpty()) {
            return $this->addCard($card);
        }

        if($remove->isNotEmpty()) {
            return $this->removeCard($card);
        }

        return false;
    }
}

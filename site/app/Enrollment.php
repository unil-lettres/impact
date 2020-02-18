<?php

namespace App;

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
    public function hasCard($card)
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
    public function addCard($card)
    {
        if (!$this->hasCard($card)) {
            $cards = collect($this->cards);

            $cards = $cards->push($card->id);

            $this->update([
                'cards' => $cards->all()
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
    public function removeCard($card)
    {
        if ($this->hasCard($card)) {
            $cards = collect($this->cards);

            $cards = $cards->reject(function ($cardId) use ($card) {
                return $cardId == $card->id;
            });

            $this->update([
                'cards' => $cards->all()
            ]);
            $this->save();

            return true;
        }

        return false;
    }

    /**
     * Add card to the enrollment if the user is part of the selected editors.
     * Remove card from the enrollment if the user is not part of the selected editors.
     *
     * @param Card $card
     * @param array $editorsId
     *
     * @return bool
     */
    public function updateCard($card, $editorsId)
    {
        // If editors is empty, remove the specified card from the enrollment cards
        if (empty($editorsId)) {
            return $this->removeCard($card);
        }

        // If the enrollment user is part of the selected editors, add the card to the enrollment cards
        if (in_array($this->user_id, $editorsId)) {
            return $this->addCard($card);
        }

        // Remove the specified card from the enrollment cards otherwise
        return $this->removeCard($card);
    }
}

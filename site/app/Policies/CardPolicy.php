<?php

namespace App\Policies;

use App\Card;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CardPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any cards.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        // TODO: add policy
    }

    /**
     * Determine whether the user can view the card.
     *
     * @param User $user
     * @param Card $card
     * @return mixed
     */
    public function view(User $user, Card $card)
    {
        // TODO: add policy
    }

    /**
     * Determine whether the user can create cards.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        // TODO: add policy
    }

    /**
     * Determine whether the user can update the card.
     *
     * @param User $user
     * @param Card $card
     * @return mixed
     */
    public function update(User $user, Card $card)
    {
        // TODO: add policy
    }

    /**
     * Determine whether the user can delete the card.
     *
     * @param User $user
     * @param Card $card
     * @return mixed
     */
    public function delete(User $user, Card $card)
    {
        // TODO: add policy
    }

    /**
     * Determine whether the user can restore the card.
     *
     * @param User $user
     * @param Card $card
     * @return mixed
     */
    public function restore(User $user, Card $card)
    {
        // TODO: add policy
    }

    /**
     * Determine whether the user can permanently delete the card.
     *
     * @param User $user
     * @param Card $card
     * @return mixed
     */
    public function forceDelete(User $user, Card $card)
    {
        // TODO: add policy
    }
}

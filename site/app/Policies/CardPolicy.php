<?php

namespace App\Policies;

use App\Card;
use App\Course;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CardPolicy
{
    use HandlesAuthorization;

    /**
     * Authorize all actions for admins
     *
     * @param $user
     * @param $ability
     *
     * @return bool
     */
    public function before($user, $ability)
    {
        if ($user->admin) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any cards.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can view the card.
     *
     * @param User $user
     * @param Card $card
     *
     * @return mixed
     */
    public function view(User $user, Card $card)
    {
        // TODO: update policy when states are added to the card

        // Only admins, teachers of the course & editors of the card can view the card
        if ($user->isTeacher($card->course) || $user->isEditor($card)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create cards.
     *
     * @param User $user
     * @param Course $course
     *
     * @return mixed
     */
    public function create(User $user, Course $course)
    {
        // Only admins & teachers of the course can create new cards
        if ($user->isTeacher($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the card.
     *
     * @param User $user
     * @param Card $card
     *
     * @return mixed
     */
    public function update(User $user, Card $card)
    {
        // TODO: update policy when states are added to the card

        // Only admins & teachers of the course can update cards
        if ($user->isTeacher($card->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the card.
     *
     * @param User $user
     * @param Card $card
     *
     * @return mixed
     */
    public function delete(User $user, Card $card)
    {
        // Only admins & teachers can delete cards
        if ($user->isTeacher($card->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the card.
     *
     * @param User $user
     * @param Card $card
     *
     * @return mixed
     */
    public function forceDelete(User $user, Card $card)
    {
        // Only admins can delete permanently cards
        return false;
    }
}

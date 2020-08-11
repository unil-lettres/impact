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
     * Determine whether the user can view any cards.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        if ($user->admin) {
            return true;
        }

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

        if ($user->admin) {
            return true;
        }

        // Only teachers of the course & editors can view the card
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
        if ($user->admin) {
            return true;
        }

        // Only teachers of the course can create new cards
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

        if ($user->admin) {
            return true;
        }

        // Only teachers of the course can update cards
        if ($user->isTeacher($card->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can forceDelete the card.
     *
     * @param User $user
     * @param Card $card
     *
     * @return mixed
     */
    public function forceDelete(User $user, Card $card)
    {
        if ($user->admin) {
            return true;
        }

        // Only teachers of the course can delete cards
        if ($user->isTeacher($card->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can unlink a file from the card
     *
     * @param User $user
     * @param Card $card
     *
     * @return mixed
     */
    public function unlinkFile(User $user, Card $card)
    {
        if ($user->admin) {
            return true;
        }

        // Only teachers of the course can unlink files
        if ($user->isTeacher($card->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the editor html from the card
     *
     * @param User $user
     * @param Card $card
     *
     * @return mixed
     */
    public function editor(User $user, Card $card)
    {
        if ($user->admin) {
            return true;
        }

        // Only teachers of the course & editors can update the editor html from the card
        if ($user->isTeacher($card->course) || $user->isEditor($card)) {
            return true;
        }

        return false;
    }
}

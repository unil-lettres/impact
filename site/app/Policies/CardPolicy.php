<?php

namespace App\Policies;

use App\Card;
use App\Course;
use App\Enums\StateType;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CardPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any cards.
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
     * @return mixed
     */
    public function view(User $user, Card $card)
    {
        if ($user->admin) {
            return true;
        }

        // Users that cannot see any box can't access the card, even managers or editors.
        if ($card->allBoxesAreHidden()) {
            return false;
        }

        // Editors of the course can view the card
        if ($user->isEditor($card)) {
            return true;
        }

        // Teachers of the course can view the card if the state is not set to the 'private' type
        if ($user->isTeacher($card->course) && $card->state?->type !== StateType::Private) {
            return true;
        }

        // Students of the course can view the card if the state is set to the 'archived' type
        if ($user->isStudent($card->course) && $card->state?->type === StateType::Archived) {
            return true;
        }

        // Students of the course can view the card, if the state has at least one public permission
        if ($user->isStudent($card->course) && $card->state?->hasPublicPermission()) {
            return true;
        }

        return false;
    }

    /**
     * Works like view policy except that the manager of the course can view
     * the card in listings.
     */
    public function index(User $user, Card $card): bool
    {
        // Teachers of the course or editors can always list the card.
        if ($user->isTeacher($card->course) || $user->isEditor($card)) {
            return true;
        }

        return $this->view($user, $card);
    }

    /**
     * Determine whether the user can create cards.
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
     * @return mixed
     */
    public function update(User $user, Card $card)
    {
        if ($user->admin) {
            return true;
        }

        // Users that cannot see any box can't update the card, even managers or editors.
        if ($card->allBoxesAreHidden()) {
            return false;
        }

        // Teachers of the course can update the card if the state is not set to the 'private' type
        if ($user->isTeacher($card->course) && $card->state?->type !== StateType::Private) {
            return true;
        }

        // Editors of the course can update card if the state is not set to the 'archived' type
        if ($user->isEditor($card) && $card->state?->type !== StateType::Archived) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can manage the card (move, clone, etc.).
     *
     * @return mixed
     */
    public function manage(User $user, Card $card)
    {
        if ($user->admin) {
            return true;
        }

        // Only teachers of the course can manage cards
        if ($user->isTeacher($card->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can forceDelete the card.
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
     * Determine whether the user can update a specific box of the card
     *
     * @return mixed
     */
    public function box(User $user, Card $card, string $box)
    {
        if ($user->admin) {
            return true;
        }

        if ($card->boxIsEditable($box)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can hide parts of the card
     *
     * @return mixed
     */
    public function hide(User $user, Card $card)
    {
        if ($user->admin) {
            return true;
        }

        // Only teachers of the course can hide parts of the card
        if ($user->isTeacher($card->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can set the parameters of the card
     *
     * @return mixed
     */
    public function parameters(User $user, Card $card)
    {
        if ($user->admin) {
            return true;
        }

        // Only teachers of the course can hide parts of the card
        if ($user->isTeacher($card->course)) {
            return true;
        }

        return false;
    }
}

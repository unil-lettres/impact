<?php

namespace App\Policies;

use App\Course;
use App\Invitation;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvitationPolicy
{
    use HandlesAuthorization;

    /**
     * Authorize all actions for admins
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
     * Determine whether the user can view any invitations.
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        // Only managers of a course can view invitations
        if ($user->enrollmentsAsManager()->isNotEmpty()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view any invitations in the admin panel.
     *
     * @return mixed
     */
    public function manage(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can view the invitation.
     *
     * @return mixed
     */
    public function view(User $user, Invitation $invitation)
    {
        if ($invitation->registered_at) {
            return false;
        }

        return $user->id === $invitation->creator_id;
    }

    /**
     * Determine whether the user can create invitations.
     *
     * @return mixed
     */
    public function create(User $user, ?Course $course)
    {
        // Only managers of a course can view the invitation creation form
        if (! $course && $user->enrollmentsAsManager()->isNotEmpty()) {
            return true;
        }

        // Only managers of the course can store new invitations
        if ($course && $user->isManager($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the invitation.
     *
     * @return mixed
     */
    public function update(User $user, Invitation $invitation)
    {
        if ($invitation->registered_at) {
            return false;
        }

        return $user->id === $invitation->creator_id;
    }

    /**
     * Determine whether the user can permanently delete the invitation.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Invitation $invitation)
    {
        if ($invitation->registered_at) {
            return false;
        }

        return $user->id === $invitation->creator_id;
    }

    /**
     * Determine whether the user can view the register form.
     *
     * @return mixed
     */
    public function register(?User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can register a new account.
     *
     * @return mixed
     */
    public function createInvitationUser(?User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can send the invitation mail.
     *
     * @return mixed
     */
    public function mail(User $user, Invitation $invitation)
    {
        if ($invitation->registered_at) {
            return false;
        }

        return $user->id === $invitation->creator_id;
    }
}

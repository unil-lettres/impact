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
     * Authorize all actions for admins.
     */
    public function before($user, $ability): ?bool
    {
        if ($user->admin) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any invitations.
     */
    public function viewAny(User $user): bool
    {
        // Only managers of a course can view invitations
        if ($user->enrollmentsAsManager()->isNotEmpty()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view any invitations in the admin panel.
     */
    public function manage(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the invitation.
     */
    public function view(User $user, Invitation $invitation): bool
    {
        if ($invitation->registered_at) {
            return false;
        }

        return $user->id === $invitation->creator_id;
    }

    /**
     * Determine whether the user can create invitations.
     */
    public function create(User $user, ?Course $course): bool
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
     */
    public function update(User $user, Invitation $invitation): bool
    {
        if ($invitation->registered_at) {
            return false;
        }

        return $user->id === $invitation->creator_id;
    }

    /**
     * Determine whether the user can permanently delete the invitation.
     */
    public function forceDelete(User $user, Invitation $invitation): bool
    {
        if ($invitation->registered_at) {
            return false;
        }

        return $user->id === $invitation->creator_id;
    }

    /**
     * Determine whether the user can view the register form.
     */
    public function register(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can register a new account.
     */
    public function createInvitationUser(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can send the invitation mail.
     */
    public function mail(User $user, Invitation $invitation): bool
    {
        if ($invitation->registered_at) {
            return false;
        }

        return $user->id === $invitation->creator_id;
    }
}

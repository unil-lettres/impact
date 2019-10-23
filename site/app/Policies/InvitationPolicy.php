<?php

namespace App\Policies;

use App\Invitation;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvitationPolicy
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
     * Determine whether the user can view any invitations.
     *
     * @param  User  $user
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the invitation.
     *
     * @param  User  $user
     * @param  Invitation  $invitation
     *
     * @return mixed
     */
    public function view(User $user, Invitation $invitation)
    {
        return $user->id === $invitation->creator_id;
    }

    /**
     * Determine whether the user can create invitations.
     *
     * @param  User  $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the invitation.
     *
     * @param  User  $user
     * @param  Invitation  $invitation
     *
     * @return mixed
     */
    public function update(User $user, Invitation $invitation)
    {
        return $user->id === $invitation->creator_id;
    }

    /**
     * Determine whether the user can delete the invitation.
     *
     * @param  User  $user
     * @param  Invitation  $invitation
     *
     * @return mixed
     */
    public function delete(User $user, Invitation $invitation)
    {
        return $user->id === $invitation->creator_id;
    }

    /**
     * Determine whether the user can restore the invitation.
     *
     * @param  User  $user
     * @param  Invitation  $invitation
     *
     * @return mixed
     */
    public function restore(User $user, Invitation $invitation)
    {
        return $user->id === $invitation->creator_id;
    }

    /**
     * Determine whether the user can permanently delete the invitation.
     *
     * @param  User  $user
     * @param  Invitation  $invitation
     *
     * @return mixed
     */
    public function forceDelete(User $user, Invitation $invitation)
    {
        return $user->id === $invitation->creator_id;
    }

    /**
     * Determine whether the user can register a new account.
     *
     * @return mixed
     */
    public function register()
    {
        return true;
    }

    /**
     * Determine whether the user can send the invitation mail.
     *
     * @param  User  $user
     * @param  Invitation  $invitation
     *
     * @return mixed
     */
    public function mail(User $user, Invitation $invitation)
    {
        return $user->id === $invitation->creator_id;
    }
}

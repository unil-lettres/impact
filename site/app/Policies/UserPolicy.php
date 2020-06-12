<?php

namespace App\Policies;

use App\Enums\UserType;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  User  $user
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
     * Determine whether the user can view any invitations in the admin panel.
     *
     * @param  User  $user
     *
     * @return mixed
     */
    public function manage(User $user)
    {
        if ($user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  User  $model
     *
     * @return mixed
     */
    public function view(User $user, User $model)
    {
        if ($user->admin) {
            return true;
        }

        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User  $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        if ($user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param  User  $model
     *
     * @return mixed
     */
    public function update(User $user, User $model)
    {
        if ($user->admin) {
            return true;
        }

        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can extend the validity of the model.
     *
     * @param  User  $user
     * @param  User  $model
     *
     * @return mixed
     */
    public function extend(User $user, User $model)
    {
        return $user->admin &&
            !$model->admin &&
            $model->type === UserType::Local;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  User  $model
     *
     * @return mixed
     */
    public function delete(User $user, User $model)
    {
        if ($user->id === $model->id) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        return false;
    }
}

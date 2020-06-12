<?php

namespace App\Policies;

use App\File;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        // TODO: add logic
    }

    /**
     * Determine whether the user can view any files in the admin panel.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function manage(User $user)
    {
        // TODO: add logic
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param File $file
     *
     * @return mixed
     */
    public function view(User $user, File $file)
    {
        // TODO: add logic
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        // TODO: add logic
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param File $file
     *
     * @return mixed
     */
    public function update(User $user, File $file)
    {
        // TODO: add logic
    }

    /**
     * Determine whether the user can forceDelete the model.
     *
     * @param User $user
     * @param File $file
     *
     * @return mixed
     */
    public function forceDelete(User $user, File $file)
    {
        // The file cannot be deleted if linked to a card
        if ($file->cards->isNotEmpty()) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        // If the file is linked to a course, then only
        // the teachers of the course can delete the file
        if ($file->course && $user->isTeacher($file->course)) {
            return true;
        }

        return false;
    }
}

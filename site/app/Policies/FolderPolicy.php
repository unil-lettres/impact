<?php

namespace App\Policies;

use App\Course;
use App\Folder;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FolderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any folders.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        // TODO: add policy checks
        return true;
    }

    /**
     * Determine whether the user can view the folder.
     *
     * @param User $user
     * @param Folder $folder
     *
     * @return mixed
     */
    public function view(User $user, Folder $folder)
    {
        // TODO: add policy checks
        return true;
    }

    /**
     * Determine whether the user can create folders.
     *
     * @param User $user
     * @param Course $course
     *
     * @return mixed
     */
    public function create(User $user, Course $course)
    {
        // TODO: add policy checks
        return true;
    }

    /**
     * Determine whether the user can update the folder.
     *
     * @param User $user
     * @param Folder $folder
     *
     * @return mixed
     */
    public function update(User $user, Folder $folder)
    {
        // TODO: add policy checks
        return true;
    }

    /**
     * Determine whether the user can delete the folder.
     *
     * @param User $user
     * @param Folder $folder
     *
     * @return mixed
     */
    public function delete(User $user, Folder $folder)
    {
        // TODO: add policy checks
        return true;
    }

    /**
     * Determine whether the user can restore the folder.
     *
     * @param User $user
     * @param Folder $folder
     *
     * @return mixed
     */
    public function restore(User $user, Folder $folder)
    {
        // TODO: add policy checks
        return true;
    }

    /**
     * Determine whether the user can permanently delete the folder.
     *
     * @param User $user
     * @param Folder $folder
     *
     * @return mixed
     */
    public function forceDelete(User $user, Folder $folder)
    {
        // TODO: add policy checks
        return true;
    }
}

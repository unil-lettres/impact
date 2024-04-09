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
     * @return mixed
     */
    public function viewAny(User $user): bool
    {
        if ($user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the folder.
     *
     * @return mixed
     */
    public function view(User $user, Folder $folder): bool
    {
        if ($user->admin) {
            return true;
        }

        // Only managers or members of the course can view the folder
        if ($user->isManager($folder->course) || $user->isMember($folder->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create folders.
     *
     * @return mixed
     */
    public function create(User $user, Course $course): bool
    {
        if ($user->admin) {
            return true;
        }

        // Only managers of the course can create new folders
        if ($user->isManager($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the folder.
     *
     * @return mixed
     */
    public function update(User $user, Folder $folder): bool
    {
        if ($user->admin) {
            return true;
        }

        // Only managers of the course can update folders
        if ($user->isManager($folder->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can manage (clone, move, etc.) the folder.
     *
     * @return mixed
     */
    public function manage(User $user, Folder $folder)
    {
        if ($user->admin) {
            return true;
        }

        // Only managers of the course can update folders
        if ($user->isManager($folder->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can forceDelete the folder.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Folder $folder): bool
    {
        if ($user->admin) {
            return true;
        }

        // Only managers of the course can delete folders
        if ($user->isManager($folder->course)) {
            return true;
        }

        return false;
    }
}

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
    public function viewAny(User $user)
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
    public function view(User $user, Folder $folder)
    {
        if ($user->admin) {
            return true;
        }

        // Only teachers or students of the course can view the folder
        if ($user->isTeacher($folder->course) || $user->isStudent($folder->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create folders.
     *
     * @return mixed
     */
    public function create(User $user, Course $course)
    {
        if ($user->admin) {
            return true;
        }

        // Only teachers of the course can create new folders
        if ($user->isTeacher($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the folder.
     *
     * @return mixed
     */
    public function update(User $user, Folder $folder)
    {
        if ($user->admin) {
            return true;
        }

        // Only teachers of the course can update folders
        if ($user->isTeacher($folder->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can forceDelete the folder.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Folder $folder)
    {
        if ($user->admin) {
            return true;
        }

        // Only teachers of the course can delete folders
        if ($user->isTeacher($folder->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can select the folder.
     *
     * @return mixed
     */
    public function select(User $user, Course $course, Folder $selected, Folder $folder = null)
    {
        // Only folders within the course can be selected
        if ($selected->course->id !== $course->id) {
            return false;
        }

        if ($folder) {
            // Cannot select own folder as parent
            if ($folder->id === $selected->id) {
                return false;
            }
        }

        if ($user->admin) {
            return true;
        }

        // Only teachers of the course can select a folder
        if ($user->isTeacher($selected->course)) {
            return true;
        }

        return false;
    }
}

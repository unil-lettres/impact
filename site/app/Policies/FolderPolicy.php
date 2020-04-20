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
        if ($user->admin) {
            return true;
        }

        return false;
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
        // Only folders within an active course can be accessed
        if(!$folder->isActive()) {
            return false;
        }

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
     * @param User $user
     * @param Course $course
     *
     * @return mixed
     */
    public function create(User $user, Course $course)
    {
        if($course->trashed()) {
            return false;
        }

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
     * @param User $user
     * @param Folder $folder
     *
     * @return mixed
     */
    public function update(User $user, Folder $folder)
    {
        // Only folders within an active course can be accessed
        if(!$folder->isActive()) {
            return false;
        }

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
     * Determine whether the user can delete the folder.
     *
     * @param User $user
     * @param Folder $folder
     *
     * @return mixed
     */
    public function delete(User $user, Folder $folder)
    {
        // Only folders within an active course can be accessed
        if(!$folder->isActive()) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        // Only teachers of the course can delete folders
        if ($user->isTeacher($folder->course)) {
            return true;
        }

        return false;
    }
}

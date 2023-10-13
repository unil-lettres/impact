<?php

namespace App\Policies;

use App\Course;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
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
     * Determine whether the user can view any courses.
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view any courses in the admin panel.
     *
     * @return mixed
     */
    public function manage(User $user)
    {
        // Only admins can manage courses
        return false;
    }

    /**
     * Determine whether the user can view the course.
     *
     * @return mixed
     */
    public function view(User $user, Course $course)
    {
        // Return true if user is enrolled in the specific course. The role is not relevant.
        if ($user->isTeacher($course) || $user->isStudent($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create courses.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        // Only admins can create courses
        return false;
    }

    /**
     * Determine whether the user can update the course.
     *
     * @return mixed
     */
    public function update(User $user, Course $course)
    {
        // Only admins can update courses
        return false;
    }

    /**
     * Determine whether the user can edit the configuration of the course.
     *
     * @return mixed
     */
    public function editConfiguration(User $user, Course $course)
    {
        // Only admins & teachers can configure courses
        if ($user->isTeacher($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can move a set of cards and folders inside
     * another folder.
     *
     * This policy is in Course and not Card or Folder because the user
     * can move a set of cards / folders from the course UI.
     *
     * By chance, the rights to move cards and folders are always the same
     * without regards of states or anything else.
     *
     * If this change, we should check the rights for every folder and cards
     * specifically.
     */
    public function moveCardOrFolder(User $user, Course $course): bool
    {
        // Only admins & teachers can move cards or folders
        if ($user->isTeacher($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the configuration of the course.
     *
     * @return mixed
     */
    public function updateConfiguration(User $user, Course $course)
    {
        // Only admins & teachers can update the configuration of the course
        if ($user->isTeacher($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can enable the course.
     *
     * @return mixed
     */
    public function enable(User $user)
    {
        // Only admins can enable courses
        return false;
    }

    /**
     * Determine whether the user can archive the course.
     *
     * @return mixed
     */
    public function archive(User $user, Course $course)
    {
        // Only admins & teachers can archive courses
        if ($user->isTeacher($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can disable the course (soft delete).
     *
     * @return mixed
     */
    public function disable(User $user, Course $course)
    {
        // Only admins & teachers can disable courses
        if ($user->isTeacher($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the course.
     *
     * @return mixed
     */
    public function restore(User $user, Course $course)
    {
        // Only admins can restore courses
        return false;
    }

    /**
     * Determine whether the user can permanently delete the course.
     *
     * @return mixed
     */
    public function forceDelete(User $user)
    {
        // Only admins can delete permanently courses
        return false;
    }

    /**
     * Determine whether the user have the ability to use the mass action (like
     * cloning multiple cards or folders) of a course.
     *
     * See moveCardOrFolder() for more details on the reason this policy is
     * in Course instead of Card or Folder.
     *
     * @return mixed
     */
    public function massActionsForCardAndFolder(User $user, Course $course)
    {
        // Only teachers and admin of the course can delete cards or folders.
        if ($user->isTeacher($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can send the mail to confirm the deletion of the course.
     *
     * @return mixed
     */
    public function mailConfirmDelete(User $user, Course $course)
    {
        // Only admins can send the mail to confirm the deletion of the course
        return false;
    }
}

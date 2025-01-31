<?php

namespace App\Policies;

use App\Course;
use App\Enums\CourseType;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any courses.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view any courses in the admin panel.
     */
    public function manage(User $user): bool
    {
        // Only admins can manage courses
        if ($user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the course.
     */
    public function view(User $user, Course $course): bool
    {
        // Return true if user is enrolled in the specific course. The role is not relevant.
        if ($user->admin || $user->isManager($course) || $user->isMember($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create courses.
     */
    public function create(User $user): bool
    {
        // Only admins can create courses
        if ($user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the course.
     */
    public function update(User $user, Course $course): bool
    {
        // Only admins can update courses
        if ($user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can edit the configuration of the course.
     */
    public function editConfiguration(User $user, Course $course): bool
    {
        // Only admins & managers can configure courses
        if ($user->admin || $user->isManager($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the configuration of the course.
     */
    public function updateConfiguration(User $user, Course $course): bool
    {
        // Only admins & managers can update the configuration of the course
        if ($user->admin || $user->isManager($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can enable the course.
     */
    public function enable(User $user): bool
    {
        // Only admins can enable courses
        if ($user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can archive the course.
     */
    public function archive(User $user, Course $course): bool
    {
        // Only admins & managers can archive courses
        if ($user->admin || $user->isManager($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can disable the course (soft delete).
     */
    public function disable(User $user, Course $course): bool
    {
        // Only admins & managers can disable courses
        if ($user->admin || $user->isManager($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the course.
     */
    public function restore(User $user, Course $course): bool
    {
        // Only admins can restore courses
        if ($user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can unsync the external course.
     */
    public function unsync(User $user, Course $course): bool
    {
        // Only admins can unsync permanently external courses
        if ($user->admin && $course->type === CourseType::External) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the course.
     */
    public function forceDelete(User $user): bool
    {
        // Only admins can delete permanently courses
        if ($user->admin) {
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
     */
    public function massActionsForCardAndFolder(User $user, Course $course): bool
    {
        // Only managers and admin of the course can delete cards or folders.
        if ($user->admin || $user->isManager($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can send the mail to confirm the deletion of the course.
     */
    public function mailConfirmDelete(User $user, Course $course): bool
    {
        // Only admins can send the mail to confirm the deletion of the course
        if ($user->admin) {
            return true;
        }

        return false;
    }
}

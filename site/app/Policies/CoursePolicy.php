<?php

namespace App\Policies;

use App\Course;
use App\Enums\EnrollmentRole;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
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
     * Determine whether the user can view any courses.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view any courses in the admin panel.
     *
     * @param User $user
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
     * @param User $user
     * @param Course $course
     * @return mixed
     */
    public function view(User $user, Course $course)
    {
        // Return true if user is enrolled in the specific course. The role is not in this case
        if ($user->enrollments()->where('course_id', $course->id)->first()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create courses.
     *
     * @param User $user
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
     * @param User $user
     * @param Course $course
     * @return mixed
     */
    public function update(User $user, Course $course)
    {
        // Only admins can update courses
        return false;
    }

    /**
     * Determine whether the user can configure the parameters of the course.
     *
     * @param User $user
     * @param Course $course
     * @return mixed
     */
    public function configure(User $user, Course $course)
    {
        // Only admin & teachers can configure courses
        if ($course->userRole($user) === EnrollmentRole::Teacher) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can enable the course.
     *
     * @param User $user
     * @return mixed
     */
    public function enable(User $user)
    {
        // Only admins can enable courses
        return false;
    }

    /**
     * Determine whether the user can disable the course (soft delete).
     *
     * @param User $user
     * @param Course $course
     * @return mixed
     */
    public function disable(User $user, Course $course)
    {
        // Only admin & teachers can disable courses
        if ($course->userRole($user) === EnrollmentRole::Teacher) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the course.
     *
     * @param User $user
     * @param Course $course
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
     * @param User $user
     * @return mixed
     */
    public function forceDelete(User $user)
    {
        // Only admins can delete courses
        return false;
    }
}

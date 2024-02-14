<?php

namespace App\Policies;

use App\Course;
use App\Enrollment;
use App\Enums\CourseType;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EnrollmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any enrollments.
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the enrollment.
     *
     * @return mixed
     */
    public function view(User $user, Enrollment $enrollment)
    {
        return false;
    }

    /**
     * Determine whether the user can find an enrollment.
     *
     * @return mixed
     */
    public function find(User $user, Enrollment $enrollment)
    {
        if ($user->admin) {
            return true;
        }

        // Only managers can find enrollments
        if ($user->isManager($enrollment->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create enrollments.
     *
     * @return mixed
     */
    public function create(User $user, Course $enrolledCourse, User $enrolledUser)
    {
        // TODO: update for invitations & mass enrollments

        // Enrolled course should be active
        if (! $enrolledCourse->isActive()) {
            return false;
        }

        // Enrolled course type should be local
        if ($enrolledCourse->type !== CourseType::Local) {
            return false;
        }

        // Enrolled user should be valid
        if (! $enrolledUser->isValid()) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        // Only managers can create enrollments
        if ($user->isManager($enrolledCourse)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the enrollment.
     *
     * @return mixed
     */
    public function update(User $user, Enrollment $enrollment)
    {
        return false;
    }

    /**
     * Determine whether the user can update the cards of the enrollment.
     *
     * @return mixed
     */
    public function cards(User $user, Enrollment $enrollment)
    {
        // TODO: update for invitations & mass enrollment

        if ($user->admin) {
            return true;
        }

        // Only managers can update the cards of an enrollment
        if ($user->isManager($enrollment->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can forceDelete the enrollment.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Enrollment $enrollment)
    {
        // Enrolled course type should be local
        if ($enrollment->course->type !== CourseType::Local) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        // User cannot delete own enrollment
        if ($user->id === $enrollment->user->id) {
            return false;
        }

        // Only managers can delete enrollments
        if ($user->isManager($enrollment->course)) {
            return true;
        }

        return false;
    }
}

<?php

namespace App\Policies;

use App\Course;
use App\Enrollment;
use App\Helpers\Helpers;
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

        // Only teachers can find enrollments
        if ($user->isTeacher($enrollment->course)) {
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

        // Enrolled user should be valid
        if (! Helpers::isUserValid($enrolledUser)) {
            return false;
        }

        // Enrolled user cannot be an admin
        if ($enrolledUser->admin) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        // Only teachers can create enrollments
        if ($user->isTeacher($enrolledCourse)) {
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

        // Only teachers can update the cards of an enrollment
        if ($user->isTeacher($enrollment->course)) {
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
        // User cannot delete own enrollment
        if ($user->id === $enrollment->user->id) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        // Only teachers can delete enrollments
        if ($user->isTeacher($enrollment->course)) {
            return true;
        }

        return false;
    }
}

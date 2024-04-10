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
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the enrollment.
     */
    public function view(User $user, Enrollment $enrollment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can find an enrollment.
     */
    public function find(User $user, Enrollment $enrollment): bool
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
     */
    public function create(User $user, Course $enrolledCourse, User $enrolledUser): bool
    {
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
     */
    public function update(User $user, Enrollment $enrollment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the cards of the enrollment.
     */
    public function cards(User $user, Enrollment $enrollment): bool
    {
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
     */
    public function forceDelete(User $user, Enrollment $enrollment): bool
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

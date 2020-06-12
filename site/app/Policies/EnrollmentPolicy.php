<?php

namespace App\Policies;

use App\Enrollment;
use App\Enums\EnrollmentRole;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EnrollmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any enrollments.
     *
     * @param User $user
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
     * @param User $user
     * @param Enrollment $enrollment
     *
     * @return mixed
     */
    public function view(User $user, Enrollment $enrollment)
    {
        return false;
    }

    /**
     * Determine whether the user can view the enrollment.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function find(User $user)
    {
        return $user->admin;
    }

    /**
     * Determine whether the user can create enrollments.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        // TODO: update for invitations & mass enrollments

        return $user->admin;
    }

    /**
     * Determine whether the user can update the enrollment.
     *
     * @param User $user
     * @param Enrollment $enrollment
     *
     * @return mixed
     */
    public function update(User $user, Enrollment $enrollment)
    {
        return false;
    }

    /**
     * Determine whether the user can update the enrollment.
     *
     * @param User $user
     * @param Enrollment $enrollment
     *
     * @return mixed
     */
    public function cards(User $user, Enrollment $enrollment)
    {
        // TODO: update for invitations & mass enrollment

        // Cannot edit the cards of an enrollment with a teacher role
        if($enrollment->role === EnrollmentRole::Teacher) {
            return false;
        }

        return $user->isTeacher($enrollment->course);
    }

    /**
     * Determine whether the user can forceDelete the enrollment.
     *
     * @param User $user
     * @param Enrollment $enrollment
     *
     * @return mixed
     */
    public function forceDelete(User $user, Enrollment $enrollment)
    {
        return $user->admin;
    }
}

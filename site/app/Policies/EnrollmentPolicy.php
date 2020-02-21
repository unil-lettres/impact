<?php

namespace App\Policies;

use App\Enrollment;
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
        // TODO: add policy
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
        // TODO: add policy
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
        // TODO: add policy
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
        // TODO: add policy
    }

    /**
     * Determine whether the user can delete the enrollment.
     *
     * @param User $user
     * @param Enrollment $enrollment
     *
     * @return mixed
     */
    public function delete(User $user, Enrollment $enrollment)
    {
        // TODO: add policy
    }

    /**
     * Determine whether the user can restore the enrollment.
     *
     * @param User $user
     * @param Enrollment $enrollment
     *
     * @return mixed
     */
    public function restore(User $user, Enrollment $enrollment)
    {
        // TODO: add policy
    }

    /**
     * Determine whether the user can permanently delete the enrollment.
     *
     * @param User $user
     * @param Enrollment $enrollment
     *
     * @return mixed
     */
    public function forceDelete(User $user, Enrollment $enrollment)
    {
        // TODO: add policy
    }
}

<?php

namespace App\Policies;

use App\Course;
use App\State;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @param Course $course
     *
     * @return mixed
     */
    public function viewAny(User $user, Course $course)
    {
        if ($user->admin) {
            return true;
        }

        // The listing of the states cannot be viewed if not within a course
        if (!$course) {
            return false;
        }

        // Only the teachers of the course can view the listing of the states
        if ($user->isTeacher($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param State $state
     * @return mixed
     */
    public function view(User $user, State $state)
    {
        // TODO: add policy logic
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        // TODO: add policy logic
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param State $state
     * @return mixed
     */
    public function update(User $user, State $state)
    {
        // TODO: add policy logic
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param State $state
     * @return mixed
     */
    public function delete(User $user, State $state)
    {
        // TODO: add policy logic
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param State $state
     * @return mixed
     */
    public function restore(User $user, State $state)
    {
        // TODO: add policy logic
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param State $state
     * @return mixed
     */
    public function forceDelete(User $user, State $state)
    {
        // TODO: add policy logic
    }
}

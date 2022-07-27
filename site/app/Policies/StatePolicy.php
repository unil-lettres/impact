<?php

namespace App\Policies;

use App\Course;
use App\Enums\StateType;
use App\State;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  User  $user
     * @param  Course  $course
     * @return mixed
     */
    public function viewAny(User $user, Course $course)
    {
        // The listing of the states cannot be viewed if not within a course
        if (! $course) {
            return false;
        }

        // Only the teachers of the course & admins can view the listing of the states
        if ($user->isTeacher($course) || $user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  State  $state
     * @return mixed
     */
    public function view(User $user, State $state)
    {
        // A state cannot be viewed if not within a course
        if (! $state->course) {
            return false;
        }

        // Teachers of the course & admins can view a state
        if ($user->isTeacher($state->course) || $user->admin) {
            return true;
        }

        // Editors of the course can view a non teacher_only state
        if ($user->isEditor($state->course) && ! $state->teachers_only) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User  $user
     * @param  Course  $course
     * @return mixed
     */
    public function create(User $user, Course $course)
    {
        // A state cannot be created if not within a course
        if (! $course) {
            return false;
        }

        // Only the teachers of the course & admins can create states
        if ($user->isTeacher($course) || $user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param  State  $state
     * @return mixed
     */
    public function update(User $user, State $state)
    {
        // A state cannot be updated if not within a course
        if (! $state->course) {
            return false;
        }

        // Only custom states can be updated
        if ($state->type != StateType::Custom) {
            return false;
        }

        // Only the teachers of the course & admins can update states
        if ($user->isTeacher($state->course) || $user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  User  $user
     * @param  State  $state
     * @return mixed
     */
    public function forceDelete(User $user, State $state)
    {
        // A state cannot be deleted if not within a course
        if (! $state->course) {
            return false;
        }

        // Only custom states can be deleted
        if ($state->type != StateType::Custom) {
            return false;
        }

        // Only the teachers of the course & admins can delete states
        if ($user->isTeacher($state->course) || $user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently change the model position.
     *
     * @param  User  $user
     * @param  State  $state
     * @return mixed
     */
    public function position(User $user, State $state)
    {
        // A state position cannot be updated if not within a course
        if (! $state->course) {
            return false;
        }

        // Only custom states position can be updated
        if ($state->type != StateType::Custom) {
            return false;
        }

        // Only the teachers of the course & admins can change a state position
        if ($user->isTeacher($state->course) || $user->admin) {
            return true;
        }

        return false;
    }
}

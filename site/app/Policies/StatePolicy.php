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
     * @return mixed
     */
    public function viewAny(User $user, Course $course): bool
    {
        // The listing of the states cannot be viewed if not within a course
        if (! $course) {
            return false;
        }

        // Only the managers of the course & admins can view the listing of the states
        if ($user->isManager($course) || $user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return mixed
     */
    public function view(User $user, State $state): bool
    {
        // A state cannot be viewed if not within a course
        if (! $state->course) {
            return false;
        }

        // Managers of the course & admins can view a state
        if ($user->isManager($state->course) || $user->admin) {
            return true;
        }

        // Holders of the course can view a non managers_only state
        if ($user->isHolder($state->course) && ! $state->managers_only) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create(User $user, Course $course): bool
    {
        // A state cannot be created if not within a course
        if (! $course) {
            return false;
        }

        // Only the managers of the course & admins can create states
        if ($user->isManager($course) || $user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update(User $user, State $state): bool
    {
        // A state cannot be updated if not within a course
        if (! $state->course) {
            return false;
        }

        // Only custom states can be updated
        if ($state->type != StateType::Custom) {
            return false;
        }

        // Only the managers of the course & admins can update states
        if ($user->isManager($state->course) || $user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return mixed
     */
    public function forceDelete(User $user, State $state): bool
    {
        // A state cannot be deleted if not within a course
        if (! $state->course) {
            return false;
        }

        // Only custom states can be deleted
        if ($state->type != StateType::Custom) {
            return false;
        }

        // Only the managers of the course & admins can delete states
        if ($user->isManager($state->course) || $user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently change the model position.
     *
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

        // Only the managers of the course & admins can change a state position
        if ($user->isManager($state->course) || $user->admin) {
            return true;
        }

        return false;
    }
}

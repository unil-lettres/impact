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
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @param Course $course
     *
     * @return mixed
     */
    public function viewAny(User $user, Course $course)
    {
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
     *
     * @return mixed
     */
    public function view(User $user, State $state)
    {
        // A state cannot be viewed if not within a course
        if (!$state->course) {
            return false;
        }

        // Teachers of the course can view a state
        if ($user->isTeacher($state->course)) {
            return true;
        }

        // Editors of the course can view a non teacher_only state
        if ($user->isEditor($state->course) && !$state->teachers_only) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @param Course $course
     *
     * @return mixed
     */
    public function create(User $user, Course $course)
    {
        // A state cannot be created if not within a course
        if (!$course) {
            return false;
        }

        // Only the teachers of the course can create states
        if ($user->isTeacher($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param State $state
     *
     * @return mixed
     */
    public function update(User $user, State $state)
    {
        // A state cannot be updated if not within a course
        if (!$state->course) {
            return false;
        }

        // Only the teachers of the course can update states
        if ($user->isTeacher($state->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param State $state
     *
     * @return mixed
     */
    public function forceDelete(User $user, State $state)
    {
        // A state cannot be deleted if not within a course
        if (!$state->course) {
            return false;
        }

        // Only the teachers of the course can delete states
        if ($user->isTeacher($state->course)) {
            return true;
        }

        return false;
    }
}

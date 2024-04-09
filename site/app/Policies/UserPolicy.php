<?php

namespace App\Policies;

use App\Course;
use App\Enums\CourseType;
use App\Enums\UserType;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny(User $user, Course $course): bool
    {
        if ($user->admin) {
            return true;
        }

        // The listing of the registered users cannot be viewed
        // if not within a course
        if (! $course) {
            return false;
        }

        // The listing of the registered users cannot be viewed
        // if the course is has an external type
        if ($course->type === CourseType::External) {
            return false;
        }

        // Only the managers of the course can view the listing of the registered users
        if ($user->isManager($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view any invitations in the admin panel.
     *
     * @return mixed
     */
    public function manage(User $user)
    {
        if ($user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return mixed
     */
    public function view(User $user, User $model): bool
    {
        if ($user->admin) {
            return true;
        }

        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create(User $user): bool
    {
        if ($user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update(User $user, User $model): bool
    {
        if ($user->admin) {
            return true;
        }

        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can extend the validity of the model.
     *
     * @return mixed
     */
    public function extend(User $user, User $model)
    {
        return $user->admin &&
            ! $model->admin &&
            $model->type === UserType::Local;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return mixed
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        return false;
    }
}

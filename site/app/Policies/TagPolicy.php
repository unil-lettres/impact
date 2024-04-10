<?php

namespace App\Policies;

use App\Course;
use App\Tag;
use App\User;

class TagPolicy
{
    /**
     * Determine whether the user can view models.
     */
    public function viewAny(User $user, Course $course): bool
    {
        // Only admins & managers can view a tag.
        if ($user->isManager($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Course $course): bool
    {
        // Only admins & managers can create a tag.
        if ($user->isManager($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tag $tag): bool
    {
        // Only admins & managers can update a tag.
        if ($user->isManager($tag->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tag $tag): bool
    {
        // Only admins & managers can delete a tag.
        if ($user->isManager($tag->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can clone tags from a course to another course.
     */
    public function clone(User $user, Course $fromCourse, Course $toCourse): bool
    {
        // Only admins & managers can clone tags to another course.
        if ($user->isManager($fromCourse) && $user->isManager($toCourse)) {
            return true;
        }

        return false;
    }
}

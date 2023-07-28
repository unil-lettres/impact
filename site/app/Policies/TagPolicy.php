<?php

namespace App\Policies;

use App\Course;
use App\Tag;
use App\User;

class TagPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function viewInCourseConfiguration(User $user, Course $course): bool
    {
        // Only admins & teachers can view a tag.
        if ($user->isTeacher($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Course $course): bool
    {
        // Only admins & teachers can update a tag.
        if ($user->isTeacher($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tag $tag): bool
    {
        // Only admins & teachers can update a tag.
        if ($user->isTeacher($tag->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tag $tag): bool
    {
        // Only admins & teachers can delete a tag.
        if ($user->isTeacher($tag->course)) {
            return true;
        }

        return false;
    }
}

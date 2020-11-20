<?php

namespace App\Observers;

use App\Course;

class CourseObserver
{
    /**
     * Handle the course "created" event.
     *
     * @param Course $course
     * @return void
     */
    public function created(Course $course)
    {
        // TODO: add default states for this course (open & public)
    }

    /**
     * Handle the course "deleting" event.
     *
     * @param Course $course
     *
     * @return void
     */
    public function deleting(Course $course)
    {
        if(!$course->isForceDeleting()) {
            // Soft delete all related cards
            $course->cards()->delete();

            // Soft delete all related files
            $course->files()->delete();

            // Soft delete all related folders
            $course->folders()->delete();

            // Soft delete all related invitations
            $course->invitations()->delete();

            // Soft delete all related states
            $course->states()->delete();
        }
    }

    /**
     * Handle the course "restored" event.
     *
     * @param Course $course
     *
     * @return void
     */
    public function restored(Course $course)
    {
        // Restore all related cards
        foreach ($course->cards()->withTrashed()->get() as $card) {
            $card->restore();
        }

        // Restore all related files
        foreach ($course->files()->withTrashed()->get() as $file) {
            $file->restore();
        }

        // Restore all related folders
        foreach ($course->folders()->withTrashed()->get() as $folder) {
            $folder->restore();
        }

        // Restore all related invitations
        foreach ($course->invitations()->withTrashed()->get() as $invitation) {
            $invitation->restore();
        }

        // Restore all related states
        foreach ($course->states()->withTrashed()->get() as $state) {
            $state->restore();
        }
    }
}

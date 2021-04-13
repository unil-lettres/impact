<?php

namespace App\Observers;

use App\Course;
use App\Enums\StatePermission;
use App\State;

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
        // Create the "private" state
        State::create([
            'name' => 'privé',
            'description' => 'La fiche n\'est visible et éditable que par le-s rédacteur-s.',
            'position' => 0,
            'course_id' => $course->id
        ]);

        // Create the "open" state
        $openState = State::create([
            'name' => 'ouvert',
            'description' => 'La fiche est visible par le-s rédacteur-s et le-s responsable-s, mais pas par les autres utilisateurs de cet espace.',
            'position' => 1,
            'course_id' => $course->id
        ]);
        $openState->updatePermissions(
            StatePermission::TeachersAndEditorsCanShowAndEdit
        );

        // Create the "public" state
        $publicState = State::create([
            'name' => 'public',
            'description' => 'La fiche est visible par tous les utilisateurs de cet espace.',
            'position' => 2,
            'course_id' => $course->id
        ]);
        $publicState->updatePermissions(
            StatePermission::AllCanShowTeachersAndEditorsCanEdit
        );

        // Create the "archived" state
        $privateState = State::create([
            'name' => 'archivé',
            'description' => 'La fiche n\'est plus éditable par le-s rédacteur-s.',
            'position' => 1000,
            'course_id' => $course->id
        ]);
        $privateState->updatePermissions(
            StatePermission::TeachersCanShowAndEditEditorsCanShow
        );
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

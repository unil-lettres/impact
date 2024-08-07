<?php

namespace App\Observers;

use App\Course;
use App\Enums\StatePermission;
use App\Enums\StateType;
use App\State;

class CourseObserver
{
    /**
     * Handle the course "created" event.
     */
    public function created(Course $course): void
    {
        // Create the "private" state
        State::create([
            'name' => trans('states.private'),
            'description' => trans('states.private_description'),
            'position' => 0,
            'type' => StateType::Private,
            'course_id' => $course->id,
        ]);

        $actions = json_decode(State::ACTIONS, true);

        // Create the "open" state with an email action
        $actions['data'] = [
            State::buildEmailAction(
                trans('states.email_subject_open'),
                trans('states.email_message_open')
            ),
        ];
        $openState = State::create([
            'name' => trans('states.open'),
            'description' => trans('states.open_description'),
            'position' => 1,
            'course_id' => $course->id,
            'actions' => $actions,
        ]);
        $openState->updatePermissions(
            StatePermission::ManagersAndHoldersCanShowAndEdit
        );

        // Create the "public" state with an email action
        $actions['data'] = [
            State::buildEmailAction(
                trans('states.email_subject_public'),
                trans('states.email_message_public')
            ),
        ];
        $publicState = State::create([
            'name' => trans('states.public'),
            'description' => trans('states.public_description'),
            'position' => 2,
            'course_id' => $course->id,
            'actions' => $actions,
        ]);
        $publicState->updatePermissions(
            StatePermission::AllCanShowManagersAndHoldersCanEdit
        );

        // Create the "archived" state
        $archivedState = State::create([
            'name' => trans('states.archived'),
            'description' => trans('states.archived_description'),
            'position' => 1000,
            'type' => StateType::Archived,
            'course_id' => $course->id,
        ]);
        $archivedState->updatePermissions(
            StatePermission::AllCanShowManagersCanEdit
        );
    }

    /**
     * Handle the course "deleting" event.
     */
    public function deleting(Course $course): void
    {
        if (! $course->isForceDeleting()) {
            // Soft delete all related enrollments
            $course->enrollments()->delete();

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

            // Soft delete all related tags
            $course->tags()->delete();
        }
    }

    /**
     * Handle the course "restored" event.
     */
    public function restored(Course $course): void
    {
        // Restore all related enrollments
        foreach ($course->enrollments()->withTrashed()->get() as $enrollment) {
            $enrollment->restore();
        }

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

        // Restore all related tags
        foreach ($course->tags()->withTrashed()->get() as $tag) {
            $tag->restore();
        }
    }
}

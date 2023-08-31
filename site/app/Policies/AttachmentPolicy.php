<?php

namespace App\Policies;

use App\Card;
use App\Course;
use App\Enums\FileStatus;
use App\File;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Str;

class AttachmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny(User $user, Course $course)
    {
        if ($user->admin) {
            return true;
        }

        // Teachers and students of the course can view the attachments
        if ($user->isTeacher($course) || $user->isStudent($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return mixed
     */
    public function view(User $user, File $file)
    {
        if ($user->admin) {
            return true;
        }

        // Teachers and students of the course can view the attachment
        if ($user->isTeacher($file->course) || $user->isStudent($file->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can forceDelete the model.
     *
     * @return mixed
     */
    public function forceDelete(User $user, File $file)
    {
        // The attachment cannot be deleted if status is "processing" or "transcoding"
        if (Str::contains($file->status, [FileStatus::Processing, FileStatus::Transcoding])) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        // Teachers of the course or editors of the card can deleted the attachment
        if ($user->isTeacher($file->course) || $user->isEditor($file->card)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can upload an attachment.
     *
     * @return mixed
     */
    public function upload(User $user, Course $course, Card $card)
    {
        if ($user->admin) {
            return true;
        }

        // Teachers of the course or editors of the card can upload a file within a card
        if ($user->isTeacher($course) || $user->isEditor($card)) {
            return true;
        }

        return false;
    }
}

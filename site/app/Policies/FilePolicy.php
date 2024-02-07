<?php

namespace App\Policies;

use App\Card;
use App\Course;
use App\Enums\FileStatus;
use App\File;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Str;

class FilePolicy
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

        // The listing of the files cannot be viewed if not within a course
        if (! $course) {
            return false;
        }

        // Only the managers of the course can view the listing of the files
        if ($user->isManager($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view any files in the admin panel.
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
    public function view(User $user, File $file)
    {
        if ($user->admin) {
            return true;
        }

        // The file cannot be viewed if not linked to a course
        if (! $file->course) {
            return false;
        }

        // Only the managers of the linked course can view the file
        if ($user->isManager($file->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update(User $user, File $file)
    {
        if ($user->admin) {
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
        // The file cannot be deleted if linked to a card
        if ($file->cards->isNotEmpty()) {
            return false;
        }

        // The file cannot be deleted if status is "processing" or "transcoding"
        if (Str::contains($file->status, [FileStatus::Processing, FileStatus::Transcoding])) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        // The file cannot be deleted if not linked to a course
        if (! $file->course) {
            return false;
        }

        // Only the managers of the linked course can deleted the file
        if ($user->isManager($file->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can upload a file.
     *
     * @return mixed
     */
    public function upload(User $user, ?Course $course, ?Card $card)
    {
        if ($user->admin) {
            return true;
        }

        // Managers can upload a file within a course
        if ($course && $user->isManager($course)) {
            return true;
        }

        // Editors can upload a file within a card
        if ($card && $user->isEditor($card)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can move the model to a specific course.
     *
     * @return mixed
     */
    public function move(User $user, File $file, Course $course)
    {
        // The file cannot be moved if linked to a card
        if ($file->cards->isNotEmpty()) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        // The file can only be moved to a course that the user teaches
        if ($user->isManager($course)) {
            return true;
        }

        return false;
    }
}

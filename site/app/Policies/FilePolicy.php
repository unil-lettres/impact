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
     */
    public function viewAny(User $user, Course $course): bool
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
     */
    public function manage(User $user): bool
    {
        if ($user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, File $file): bool
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
     */
    public function update(User $user, File $file): bool
    {
        if ($user->admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can forceDelete the model.
     */
    public function forceDelete(User $user, File $file): bool
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
     */
    public function upload(User $user, ?Course $course, ?Card $card): bool
    {
        if ($user->admin) {
            return true;
        }

        // Managers can upload a file within a course
        if ($course && $user->isManager($course)) {
            return true;
        }

        // Holders can upload a file within a card
        if ($card && $user->isHolder($card)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can download a file.
     */
    public function download(User $user, ?File $file): bool
    {
        // The file cannot be downloaded if not present
        if (! $file) {
            return false;
        }

        // The file cannot be download if status is not "ready"
        if ($file->status !== FileStatus::Ready) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        // Only the managers of the linked course can download the file
        if ($file->course && $user->isManager($file->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view a file url.
     */
    public function url(User $user, ?File $file): bool
    {
        // The file url cannot be shown if the file is not present
        if (! $file) {
            return false;
        }

        // The file url cannot be shown if status is not "ready"
        if ($file->status !== FileStatus::Ready) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        // Only the managers of the linked course can view the file url
        if ($file->course && $user->isManager($file->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can move the model to a specific course.
     */
    public function move(User $user, File $file, Course $course): bool
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

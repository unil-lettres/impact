<?php

namespace App\Policies;

use App\Card;
use App\Course;
use App\Enums\CardBox;
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
     */
    public function viewAny(User $user, Course $course): bool
    {
        if ($user->admin) {
            return true;
        }

        // Managers and members of the course can view the attachments
        if ($user->isManager($course) || $user->isMember($course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, File $attachment): bool
    {
        if ($user->admin) {
            return true;
        }

        // Managers and members of the course can view the attachment
        if ($user->isManager($attachment->course) || $user->isMember($attachment->course)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can forceDelete the model.
     */
    public function forceDelete(User $user, File $attachment): bool
    {
        // The attachment cannot be deleted if status is "processing" or "transcoding"
        if (Str::contains($attachment->status, [FileStatus::Processing, FileStatus::Transcoding])) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        // Managers of the course or holders of the card can deleted
        // the attachment if the attachments box is editable
        if (
            ($user->isManager($attachment->course) || $user->isHolder($attachment->card))
            && $attachment->card->boxIsEditable(CardBox::Box5)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can upload an attachment.
     */
    public function upload(User $user, Course $course, Card $card): bool
    {
        if ($user->admin) {
            return true;
        }

        // Managers of the course or holders of the card can deleted
        // the attachment if the attachments box is editable
        if (
            ($user->isManager($course) || $user->isHolder($card))
            && $card->boxIsEditable(CardBox::Box5)
        ) {
            return true;
        }

        return false;
    }
}

<?php

namespace App\Observers;

use App\Invitation;
use App\User;

class UserObserver
{
    /**
     * Handle the file "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Remove the invitation associated with the user email address
        $invitations = Invitation::where('email', $user->email);

        $invitations?->forceDelete();
    }
}

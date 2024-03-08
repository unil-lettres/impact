<?php

namespace App\Rules;

use App\Invitation;
use App\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class InvitationUniqueness implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Invitation has been sent, but not yet registered
        $isAlreadyInvited = Invitation::where('email', $value)
            ->active()
            ->exists();
        if ($isAlreadyInvited) {
            $fail(trans('invitations.already.pending'));
        }

        // User account already exists
        $isAlreadyUser = User::where('email', $value)
            ->exists();
        if ($isAlreadyUser) {
            $fail(trans('invitations.user.exists'));
        }

        // Invitation is marked as registered, but no related user account exists
        $isAlreadyRegistered = Invitation::where('email', $value)
            ->registered()
            ->exists();
        if ($isAlreadyRegistered) {
            $fail(trans('invitations.already.registered'));
        }
    }
}

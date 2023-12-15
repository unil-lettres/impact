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
        $isAlreadyInvited = Invitation::where('email', $value)
            ->whereNull('registered_at')
            ->exists();
        if ($isAlreadyInvited) {
            $fail(trans('invitations.already.pending'));
        }

        $isAlreadyUser = User::where('email', $value)
            ->exists();
        if ($isAlreadyUser) {
            $fail(trans('invitations.user.exists'));
        }
    }
}

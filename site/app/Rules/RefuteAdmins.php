<?php

namespace App\Rules;

use App\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RefuteAdmins implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::find($value);

        if (! $user) {
            $fail('Operation not permitted for this admin user.');
        }

        if ($user->admin) {
            $fail('Operation not permitted for this admin user.');
        }
    }
}

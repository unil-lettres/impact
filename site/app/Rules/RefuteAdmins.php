<?php

namespace App\Rules;

use App\User;
use Illuminate\Contracts\Validation\Rule;

class RefuteAdmins implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user = User::find($value);

        if(!$user) {
            return false;
        }

        // If the user is an admin the validation rule should return false,
        // if the user is not and admin the validation rule should return true.
        return $user->admin ? false : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Operation not permitted for this admin user.';
    }
}

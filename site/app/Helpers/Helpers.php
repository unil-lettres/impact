<?php

namespace App\Helpers;

use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

class Helpers {
    /**
     * Return current local
     *
     * @return string $local
     */
    public static function currentLocal() {

        if (session()->has('locale')) {
            return session()->get('locale');
        }

        return App::getLocale() ?? '';
    }

    /**
     * Check the validity of a user account
     *
     * @param User $user
     *
     * @return boolean $validity
     */
    public static function isUserValid(User $user) {
        // Check if user is an admin
        if($user->admin) {
            return true;
        }

        // Check if user account has an expiration date
        if(is_null($user->validity)) {
            return true;
        }

        // Check if user account is still valid
        $validity = Carbon::instance($user->validity);
        if($validity->isFuture()) {
            return true;
        }

        return false;
    }
}

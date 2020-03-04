<?php

namespace App\Helpers;

use App\Enums\UserType;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class Helpers {
    /**
     * Return current local
     *
     * @return string
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
     * @return boolean
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

    /**
     * Check if the user account type is local
     *
     * @param User $user
     *
     * @return boolean
     */
    public static function isUserLocal(User $user) {
        // Check if user has a local account type
        if($user->type === UserType::Local) {
            return true;
        }

        return false;
    }

    /**
     * Truncate string
     *
     * @param string $string
     * @param int $limit
     *
     * @return string
     */
    public static function truncate($string, $limit = 50) {
        return Str::limit($string, $limit, $end = '...');
    }
}

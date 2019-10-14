<?php

namespace App\Helpers;

use App\Models\Request;
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
}

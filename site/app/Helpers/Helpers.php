<?php

namespace App\Helpers;

use App\Course;
use App\Enums\CourseType;
use App\Enums\UserType;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
     * Check if the course type is local
     *
     * @param Course $course
     *
     * @return boolean
     */
    public static function isCourseLocal(Course $course) {
        // Check if course has a local type
        if($course->type === CourseType::Local) {
            return true;
        }

        return false;
    }

    /**
     * Truncate a string
     *
     * @param string $string
     * @param int $limit
     *
     * @return string
     */
    public static function truncate($string, $limit = 50) {
        return Str::limit($string, $limit, $end = '...');
    }

    /**
     * Get the translated course type
     *
     * @param string $type
     *
     * @return string
     */
    public static function courseType(string $type) {
        switch ($type) {
            case CourseType::External:
                return trans('courses.external');
            case CourseType::Local:
            default:
                return trans('courses.local');
        }
    }

    /**
     * Generate HTML for given breadcrumbs
     *
     * The breadcrumbs parameter should be a Collection and should
     * contain a path as the key, and a name as the value.
     * @param Collection $breadcrumbs
     *
     * @return string
     */
    public static function breadcrumbsHtml(Collection $breadcrumbs) {
        $html = "";
        foreach ($breadcrumbs as $path => $name) {
            $html .= "<a href=\"" . $path . "\">" . Helpers::truncate($name, 25) . "</a>";

            if ($breadcrumbs->last() !== $name) {
                $html .= "<span> / </span>";
            }
        }

        return $html;
    }
}

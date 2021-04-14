<?php

namespace App\Helpers;

use App\Card;
use App\Course;
use App\Enums\CourseType;
use App\Enums\FileStatus;
use App\Enums\FileType;
use App\Enums\StatePermission;
use App\Enums\UserType;
use App\File;
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
     * Check if the course type is external
     *
     * @param Course $course
     *
     * @return boolean
     */
    public static function isCourseExternal(Course $course) {
        // Check if course has an external type
        if($course->type === CourseType::External) {
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
     * Get the translated file type
     *
     * @param string $type
     *
     * @return string
     */
    public static function fileType(string $type) {
        switch ($type) {
            case FileType::Video:
                return trans('files.video');
            case FileType::Audio:
                return trans('files.audio');
            case FileType::Document:
                return trans('files.document');
            case FileType::Image:
                return trans('files.image');
            default:
                return trans('files.other');
        }
    }

    /**
     * Get the translated file status
     *
     * @param string $status
     *
     * @return string
     */
    public static function fileStatus(string $status) {
        switch ($status) {
            case FileStatus::Transcoding:
                return trans('files.transcoding');
            case FileStatus::Ready:
                return trans('files.ready');
            case FileStatus::Failed:
                return trans('files.failed');
            case FileStatus::Processing:
            default:
                return trans('files.processing');
        }
    }

    /**
     * Get the file status html badge
     *
     * @param string $status
     *
     * @return string
     */
    public static function fileStatusBadge(string $status) {
        switch ($status) {
            case FileStatus::Ready:
                return '<span class="badge badge-success">' . Helpers::fileStatus($status) . '</span>';
            case FileStatus::Failed:
                return '<span class="badge badge-danger">' . Helpers::fileStatus($status) . '</span>';
            case FileStatus::Transcoding:
            case FileStatus::Processing:
            default:
                return '<span class="badge badge-warning">' . Helpers::fileStatus($status) . '</span>';
        }
    }

    /**
     * Get file url for given filename
     *
     * @param string $filename
     *
     * @return string
     */
    public static function fileUrl(string $filename) {
        return asset('storage/uploads/files/' . $filename);
    }

    /**
     * Check whether the file is processed and ready
     *
     * @param File $file
     *
     * @return bool
     */
    public static function isFileReady(File $file) {
        if($file->status === FileStatus::Ready) {
            return true;
        }

        return false;
    }

    /**
     * Check whether the file has the failed status
     *
     * @param File $file
     *
     * @return bool
     */
    public static function isFileFailed(File $file) {
        if($file->status === FileStatus::Failed) {
            return true;
        }

        return false;
    }

    /**
     * Generate HTML for given breadcrumbs
     *
     * The breadcrumbs parameter should be a Collection and should
     * contain a path as the key, and a name as the value
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

    /**
     * Generate HTML to list all the cards of a file
     *
     * @param File $file
     *
     * @return string
     */
    public static function fileCards(File $file) {
        $html = '';

        foreach ($file->cards as $card) {
            $html .= '<div><a href="' . route('cards.show', $card->id) . '">' . $card->title . '</a></div>';
        }

        return $html;
    }

    /**
     * Return the "used" string if the file is liked to card(s) or
     * return the "unused" string if the file is not linked to card(s)
     *
     * @param File $file
     *
     * @return string
     */
    public static function fileState(File $file) {
        return $file->isUsed() ? 'used' : 'unused';
    }

    /**
     * Return whether the card has an external media link
     *
     * @param Card $card
     *
     * @return boolean
     */
    public static function hasExternalLink(Card $card) {
        return empty(trim($card['options']['box1']['link'])) ? false : true;
    }

    /**
     * Return whether the card has an external media link
     *
     * @param Card $card
     *
     * @return string|null
     */
    public static function getExternalLink(Card $card) {
        if(!Helpers::hasExternalLink($card)) {
            return null;
        }

        return trim($card['options']['box1']['link']);
    }

    /**
     * Return whether the card has a internal or external media source
     *
     * @param Card $card
     * @return boolean
     */
    public static function hasSource(Card $card) {
        if($card->file) {
            return true;
        }

        if(Helpers::hasExternalLink($card)) {
            return true;
        }

        return false;
    }

    /**
     * Return whether the card has a transcription
     *
     * @param Card $card
     * @return boolean
     */
    public static function hasTranscription(Card $card) {
        if($card->box2['data']) {
            return true;
        }

        return false;
    }

    /**
     * Return whether the specified box should be hidden from the view
     *
     * @param Card $card
     * @param string $box
     * @return boolean|null
     */
    public static function isHidden(Card $card, string $box) {
        $options = $card->options;

        if (!array_key_exists($box, $options)) {
            return null;
        }

        if (!is_array($options[$box])) {
            return null;
        }

        if (!array_key_exists('hidden', $options[$box])) {
            return null;
        }

        return $options[$box]['hidden'];
    }

    /**
     * Return the permission label
     *
     * @param int $permission (App\Enums\StatePermission)
     * @return string
     */
    public static function permissionLabel(int $permission): string
    {
        switch ($permission) {
            case StatePermission::TeachersCanShowAndEditEditorsCanShow:
                return 'Visible par le(s) rédacteur(s) et les responsables, modifiable par les responsables seulement';
            case StatePermission::EditorsCanShowAndEdit:
                return 'Visible et modifiable par le(s) rédacteur(s) seulement';
            case StatePermission::TeachersAndEditorsCanShowAndEdit:
                return 'Visible et modifiable par le(s) rédacteur(s) et les responsables';
            case StatePermission::AllCanShowTeachersAndEditorsCanEdit:
                return 'Visible par tous et modifiable par le(s) rédacteur(s) et les responsables';
            case StatePermission::AllCanShowTeachersCanEdit:
                return 'Visible par tous et modifiable par les responsables seulement';
            case StatePermission::TeachersCanShowAndEdit:
                return 'Visible et modifiable par les responsables seulement';
            case StatePermission::None:
            default:
                return 'No permission defined';
        }
    }
}

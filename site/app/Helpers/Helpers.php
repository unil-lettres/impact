<?php

namespace App\Helpers;

use App\Card;
use App\Course;
use App\Enums\CourseType;
use App\Enums\FileStatus;
use App\Enums\FileType;
use App\Enums\StatePermission;
use App\Enums\StateType;
use App\Enums\UserType;
use App\File;
use App\State;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Helpers
{
    /**
     * Return current local
     */
    public static function currentLocal(): string
    {
        if (session()->has('locale')) {
            return session()->get('locale');
        }

        return App::getLocale() ?? '';
    }

    /**
     * Check the validity of a user account
     */
    public static function isUserValid(User $user): bool
    {
        // Check if user is an admin
        if ($user->admin) {
            return true;
        }

        // Check if user account has an expiration date
        if (is_null($user->validity)) {
            return true;
        }

        // Check if user account is still valid
        $validity = Carbon::instance($user->validity);
        if ($validity->isFuture()) {
            return true;
        }

        return false;
    }

    /**
     * Check if the user account type is local
     */
    public static function isUserLocal(User $user): bool
    {
        // Check if user has a local account type
        if ($user->type === UserType::Local) {
            return true;
        }

        return false;
    }

    /**
     * Check if the course type is local
     */
    public static function isCourseLocal(Course $course): bool
    {
        // Check if course has a local type
        if ($course->type === CourseType::Local) {
            return true;
        }

        return false;
    }

    /**
     * Check if the course type is external
     */
    public static function isCourseExternal(Course $course): bool
    {
        // Check if course has an external type
        if ($course->type === CourseType::External) {
            return true;
        }

        return false;
    }

    /**
     * Truncate a string
     */
    public static function truncate(string $string, int $limit = 50): string
    {
        return Str::limit($string, $limit, $end = '...');
    }

    /**
     * Get the translated course type
     */
    public static function courseType(string $type): string
    {
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
     */
    public static function fileType(string $type): string
    {
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
     */
    public static function fileStatus(string $status): string
    {
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
     */
    public static function fileStatusBadge(string $status): string
    {
        switch ($status) {
            case FileStatus::Ready:
                return '<span class="badge bg-success">'.self::fileStatus($status).'</span>';
            case FileStatus::Failed:
                return '<span class="badge bg-danger">'.self::fileStatus($status).'</span>';
            case FileStatus::Transcoding:
            case FileStatus::Processing:
            default:
                return '<span class="badge bg-warning">'.self::fileStatus($status).'</span>';
        }
    }

    /**
     * Get file url for given filename
     */
    public static function fileUrl(string $filename): string
    {
        return asset('storage/uploads/files/'.$filename);
    }

    /**
     * Check whether the file is processed and ready
     */
    public static function isFileReady(File $file): bool
    {
        if ($file->status === FileStatus::Ready) {
            return true;
        }

        return false;
    }

    /**
     * Check whether the file has the failed status
     */
    public static function isFileFailed(File $file): bool
    {
        if ($file->status === FileStatus::Failed) {
            return true;
        }

        return false;
    }

    /**
     * Generate HTML for given breadcrumbs
     *
     * The breadcrumbs parameter should be a Collection and should
     * contain a path as the key, and a name as the value
     */
    public static function breadcrumbsHtml(Collection $breadcrumbs): string
    {
        $html = '';
        foreach ($breadcrumbs as $path => $name) {
            $html .= '<a class="legacy" href="'.$path.'">'.self::truncate($name, 25).'</a>';

            if ($breadcrumbs->last() !== $name) {
                $html .= '<span> / </span>';
            }
        }

        return $html;
    }

    /**
     * Generate HTML to list all the cards of a file
     */
    public static function fileCards(File $file): string
    {
        $html = '';

        foreach ($file->cards as $card) {
            $html .= '<div><a class="legacy" href="'.route('cards.show', $card->id).'">'.$card->title.'</a></div>';
        }

        return $html;
    }

    /**
     * Return the "used" string if the file is liked to card(s) or
     * return the "unused" string if the file is not linked to card(s)
     */
    public static function fileState(File $file): string
    {
        return $file->isUsed() ? 'used' : 'unused';
    }

    /**
     * Return whether the card has an external media link
     */
    public static function hasExternalLink(Card $card): bool
    {
        return empty(trim($card['options']['box1']['link'])) ? false : true;
    }

    /**
     * Return whether the card has an external media link
     */
    public static function getExternalLink(Card $card): string|null
    {
        if (! self::hasExternalLink($card)) {
            return null;
        }

        return trim($card['options']['box1']['link']);
    }

    /**
     * Return whether the card has a internal or external media source
     */
    public static function hasSource(Card $card): bool
    {
        if ($card->file) {
            return true;
        }

        if (self::hasExternalLink($card)) {
            return true;
        }

        return false;
    }

    /**
     * Return whether the card has a transcription
     */
    public static function hasTranscription(Card $card): bool
    {
        if ($card->box2['data']) {
            return true;
        }

        return false;
    }

    /**
     * Return whether the specified box should be hidden from the view
     */
    public static function isHidden(Card $card, string $box): bool|null
    {
        $options = $card->options;

        if (! array_key_exists($box, $options)) {
            return null;
        }

        if (! is_array($options[$box])) {
            return null;
        }

        if (! array_key_exists('hidden', $options[$box])) {
            return null;
        }

        return $options[$box]['hidden'];
    }

    /**
     * Return the permission label
     *
     * @param  int  $permission (App\Enums\StatePermission)
     */
    public static function permissionLabel(int $permission): string
    {
        switch ($permission) {
            case StatePermission::TeachersCanShowAndEditEditorsCanShow:
                return trans('states.permission1');
            case StatePermission::EditorsCanShowAndEdit:
                return trans('states.permission2');
            case StatePermission::TeachersAndEditorsCanShowAndEdit:
                return trans('states.permission3');
            case StatePermission::AllCanShowTeachersAndEditorsCanEdit:
                return trans('states.permission4');
            case StatePermission::AllCanShowTeachersCanEdit:
                return trans('states.permission5');
            case StatePermission::TeachersCanShowAndEdit:
                return trans('states.permission6');
            case StatePermission::None:
            default:
                return trans('states.permission0');
        }
    }

    /**
     * Return whether the state type is considered read only or not
     */
    public static function isStateReadOnly(State $state): bool
    {
        switch ($state->type) {
            case StateType::Archived:
            case StateType::Private:
                return true;
            case StateType::Custom:
            default:
                return false;
        }
    }

    /**
     * Return whether the state has an action of a certain type
     *
     * @param  string  $type (App\Enums\ActionType)
     */
    public static function stateHasActionOfType(State $state, string $type): bool
    {
        if (! Helpers::stateHasActions($state)) {
            return false;
        }

        foreach ($state->actions['data'] as $action) {
            if (! isset($action['type'])) {
                return false;
            }

            if ($action['type'] === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return whether the state has one or more action(s)
     */
    public static function stateHasActions(State $state): bool
    {
        if (! $state->actions) {
            return false;
        }

        if (! isset($state->actions['data'])) {
            return false;
        }

        if (empty($state->actions['data'])) {
            return false;
        }

        return true;
    }

    /**
     * Return whether the current user is allowed to see the card's box
     */
    public static function boxIsVisible(Card $card, string $box): bool
    {
        if (! $card->state) {
            return false;
        }

        if (! $card->state->getPermission($box)) {
            return false;
        }

        // Check if user role is allowed to see the box
        return match ($card->state->getPermission($box)) {
            StatePermission::TeachersCanShowAndEditEditorsCanShow => Auth::user()->isTeacher($card->course) || Auth::user()->isEditor($card),
            StatePermission::EditorsCanShowAndEdit => Auth::user()->isEditor($card),
            StatePermission::TeachersAndEditorsCanShowAndEdit => Auth::user()->isTeacher($card->course) || Auth::user()->isEditor($card),
            StatePermission::AllCanShowTeachersAndEditorsCanEdit, StatePermission::AllCanShowTeachersCanEdit => Auth::user()->isTeacher($card->course) || Auth::user()->isEditor($card) || Auth::user()->isStudent($card->course),
            StatePermission::TeachersCanShowAndEdit => Auth::user()->isTeacher($card->course),
            default => Auth::user()->admin,
        };
    }

    /**
     * Return whether the current user is allowed to edit the card's box
     */
    public static function boxIsEditable(Card $card, string $box): bool
    {
        if (! $card->state) {
            return false;
        }

        if (! $card->state->getPermission($box)) {
            return false;
        }

        // Check if user role is allowed to edit the box
        return match ($card->state->getPermission($box)) {
            StatePermission::TeachersCanShowAndEditEditorsCanShow => Auth::user()->isTeacher($card->course),
            StatePermission::EditorsCanShowAndEdit => Auth::user()->isEditor($card),
            StatePermission::TeachersAndEditorsCanShowAndEdit, StatePermission::AllCanShowTeachersAndEditorsCanEdit => Auth::user()->isTeacher($card->course) || Auth::user()->isEditor($card),
            StatePermission::AllCanShowTeachersCanEdit, StatePermission::TeachersCanShowAndEdit => Auth::user()->isTeacher($card->course),
            default => Auth::user()->admin,
        };
    }

    /**
     * Return whether the current state of the card should be displayed in read only mode
     */
    public static function isStateSelectEditable(Card $card): bool
    {
        if (! $card->state) {
            return false;
        }

        if ($card->state->type === StateType::Archived && ! Auth::user()->isTeacher($card->course)) {
            return false;
        }

        return true;
    }
}

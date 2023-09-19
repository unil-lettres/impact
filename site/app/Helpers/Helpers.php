<?php

namespace App\Helpers;

use App\Card;
use App\Course;
use App\Enums\CourseType;
use App\Enums\FileStatus;
use App\Enums\FileType;
use App\Enums\StateType;
use App\Enums\UserType;
use App\File;
use App\Folder;
use App\State;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        return match ($type) {
            CourseType::External => trans('courses.external'),
            default => trans('courses.local'),
        };
    }

    /**
     * Get the translated file type
     */
    public static function fileType(string $type): string
    {
        return match ($type) {
            FileType::Video => trans('files.video'),
            FileType::Audio => trans('files.audio'),
            FileType::Document => trans('files.document'),
            FileType::Image => trans('files.image'),
            default => trans('files.other'),
        };
    }

    /**
     * Get the translated file status
     */
    public static function fileStatus(string $status): string
    {
        return match ($status) {
            FileStatus::Transcoding => trans('files.transcoding'),
            FileStatus::Ready => trans('files.ready'),
            FileStatus::Failed => trans('files.failed'),
            default => trans('files.processing'),
        };
    }

    /**
     * Get the file status html badge
     */
    public static function fileStatusBadge(string $status): string
    {
        return match ($status) {
            FileStatus::Ready => '<span class="badge bg-success">'.self::fileStatus($status).'</span>',
            FileStatus::Failed => '<span class="badge bg-danger">'.self::fileStatus($status).'</span>',
            default => '<span class="badge bg-warning">'.self::fileStatus($status).'</span>',
        };
    }

    /**
     * Check whether the file has a specific status
     *
     * @param  string  $status (App\Enums\FileStatus)
     */
    public static function isFileStatus(File $file, string $status): bool
    {
        if ($file->status === $status) {
            return true;
        }

        return false;
    }

    /**
     * Get file url for given filename
     */
    public static function fileUrl(string $filename): string
    {
        return asset('storage/uploads/files/'.$filename);
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
        $user = Auth::user();

        foreach ($file->cards as $card) {
            $html .= match (true) {
                // Append the card title without a link because teachers cannot access private cards
                ! $user->admin && $user->isTeacher($card->course) && $card->state?->type === StateType::Private => '<div>'.$card->title.'</div>',
                default => '<div><a class="legacy" href="'.route('cards.show', $card->id).'">'.$card->title.'</a></div>',
            };
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
        return ! empty(trim($card['options']['box1']['link'] ?? ''));
    }

    /**
     * Return whether the card has an external media link
     */
    public static function getExternalLink(Card $card): ?string
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
    public static function isHidden(Card $card, string $box): ?bool
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
     * Return whether the state type is considered read only or not
     */
    public static function isStateReadOnly(State $state): bool
    {
        return match ($state->type) {
            StateType::Archived, StateType::Private => true,
            default => false,
        };
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

    /**
     * Return whether the current state is referenced by a card
     */
    public static function isStateReferenced(State $state): bool
    {
        return ! $state->cards->isEmpty();
    }

    /**
     * Return a collection of courses from user's enrollments.
     *
     * Return all courses if user is admin.
     *
     * @param  Collection<Course>  $excludeCourses Collection of courses that
     * should not be present in the collection results.
     */
    public static function fetchUserCourses(Collection $excludeCourses = null): Collection
    {
        $excludeCourses = $excludeCourses ?? collect([]);

        return (match (Auth::user()->admin) {
            true => Course::all(),
            default => Auth::user()
                ->enrollmentsAsTeacher()
                ->map(fn ($enrollment) => $enrollment->course),
        })->whereNotIn('id', $excludeCourses->pluck('id'));
    }

    /**
     * Return the next position for a card or folder based on existing cards
     * or folders.
     *
     * @param  Course The course.
     * @param  Folder The parent folder (if exists).
     */
    public static function getNextPositionForCourse(Course $course, Folder $parent = null)
    {
        $maxCardPosition = Card::where('course_id', $course->id)
            ->where('folder_id', $parent->id ?? null)
            ->max('position');

        $maxFolderPosition = Folder::where('course_id', $course->id)
            ->where('parent_id', $parent->id ?? null)
            ->max('position');

        if (is_null($maxCardPosition ?? $maxFolderPosition)) {
            return 0;
        }

        $position = max($maxCardPosition, $maxFolderPosition);

        return $position + 1;
    }

    /**
     * Return a collection of cards and folders contained inside the given
     * folder.
     *
     * If no folder are given, return the root rows.
     *
     * @param  Course The course.
     * @param  Folder The folder.
     */
    public static function getFolderContent(
        Course $course,
        Folder $folder = null,
        string $sortColumn = 'position',
        string $sortDirection = 'asc',
        Collection $filterTags = null,
    ): Collection {

        if (is_null($filterTags)) $filterTags = collect([]);

        // TODO recupérer uniquement les cartes dont l'utilisateur peut avoir accès.
        return collect([])
            ->concat(
                Folder::where('course_id', $course->id)
                    ->where('parent_id', $folder?->id)
                    ->get()
            )
            ->concat(
                Card::with('tags')->with('state')->with('folder')
                    ->where('course_id', $course->id)
                    ->where('folder_id', $folder?->id)
                    ->where(function ($query) use ($filterTags) {
                        if ($filterTags->isNotEmpty()) {
                            return $query->whereHas('tags', function ($query) use ($filterTags) {
                                $query->whereIn('tag_id', $filterTags);
                            });
                        }
                        return $query;
                    })
                    ->get()
            )
            ->sortBy([
                [$sortColumn, $sortDirection],
                ['id', 'asc'], // Should not happens since position should be unique.
            ])
            ->values();
    }
}

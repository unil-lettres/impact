<?php

namespace App\Helpers;

use App\Card;
use App\Course;
use App\Enums\CardBox;
use App\Enums\CourseType;
use App\Enums\FileStatus;
use App\Enums\FileType;
use App\Enums\FinderRowType;
use App\Enums\StateType;
use App\Enums\TranscriptionType;
use App\Enums\UserType;
use App\File;
use App\Folder;
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
    public static function fileStatusBadge(File $file): string
    {
        return match ($file->status) {
            FileStatus::Ready => '<span class="badge bg-success">'.self::fileStatus($file->status).'</span>',
            FileStatus::Failed => '<span class="badge bg-danger">'.self::fileStatus($file->status).'</span>',
            default => '<span class="badge bg-warning">'
                .self::fileStatus($file->status)
                .($file->progress ? ' ('.$file->progress.'%)' : '')
                .'</span>',
        };
    }

    /**
     * Check whether the file has a specific status
     *
     * @param  string  $status (App\Enums\FileStatus)
     */
    public static function isFileStatus(?File $file, string $status): bool
    {
        if ($file?->status === $status) {
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
    public static function cardHasExternalLink(Card $card): bool
    {
        return ! empty(trim($card['options']['box1']['link'] ?? ''));
    }

    /**
     * Return the card external media link
     */
    public static function getCardExternalLink(Card $card): ?string
    {
        if (! self::cardHasExternalLink($card)) {
            return null;
        }

        return trim($card['options']['box1']['link']);
    }

    /**
     * Return whether the card has a internal or external media source
     */
    public static function cardHasSource(Card $card): bool
    {
        if ($card->file) {
            return true;
        }

        if (self::cardHasExternalLink($card)) {
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
     * Return a collection of courses from teacher's enrollments.
     *
     * Return all courses if user is admin.
     *
     * @param  Collection<Course>  $excludeCourses Collection of courses that
     * should not be present in the collection results.
     */
    public static function fetchCoursesAsTeacher(Collection $excludeCourses = null): Collection
    {
        $excludeCourses = $excludeCourses ?? collect([]);

        return (match (Auth::user()->admin) {
            true => Course::all(),
            default => Auth::user()
                ->enrollmentsAsTeacher()
                ->map(fn ($enrollment) => $enrollment->course),
        })->whereNotIn('id', $excludeCourses->pluck('id'))->sortBy('name');
    }

    /**
     * Return the last available position for a card or folder based on existing
     * content of the parent. The position of the given card or folder will not
     * be taken into account.
     */
    public static function findLastPositionInParent(
        Card|Folder $cardOrFolder,
    ): int {
        $cardId = $folderId = null;
        if ($cardOrFolder instanceof Card) {
            $parentId = $cardOrFolder->folder_id;
            $cardId = $cardOrFolder->id;
        } else {
            $parentId = $cardOrFolder->parent_id;
            $folderId = $cardOrFolder->id;
        }

        $maxCardPosition = Card::where('course_id', $cardOrFolder->course_id)
            ->where('folder_id', $parentId)
            ->where('id', '!=', $cardId)
            ->max('position');

        $maxFolderPosition = Folder::where('course_id', $cardOrFolder->course_id)
            ->where('parent_id', $parentId)
            ->where('id', '!=', $folderId)
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
     * Cards are filtered by given filters.
     *
     * Items are sorted by given sort column and direction.
     *
     * If no folder are given, return the root items.
     */
    public static function getFolderContent(
        Course $course,
        Collection $filters,
        Collection $filterSearchBoxes,
        Folder $folder = null,
        string $sortColumn = 'position',
        string $sortDirection = 'asc',
    ): Collection {

        $cards = Card::with('tags')->with('state')->with('folder')
            ->where('course_id', $course->id)
            ->where('folder_id', $folder?->id)
            ->where(function ($query) use ($filters) {
                // Filter specified tags id.
                $filterTags = $filters->get('tag');
                if ($filterTags->isNotEmpty()) {
                    return $query->whereHas('tags', function ($query) use ($filterTags) {
                        $query->whereIn('tag_id', $filterTags);
                    });
                }

                return $query;
            })
            ->where(function ($query) use ($filters) {
                // Filter specified states id.
                $filterStates = $filters->get('state');
                if ($filterStates->isNotEmpty()) {
                    return $query->whereIn('state_id', $filterStates);
                }

                return $query;
            })
            ->get();

        // Filter specified editors id.
        // Due to how editors are implemented, we do this directly in the
        // collection.
        if ($filters->get('editor')->isNotEmpty()) {
            $cards = $cards->filter(
                fn ($card) => $card
                    ->editors()
                    ->pluck('id')
                    ->intersect($filters->get('editor'))
                    ->isNotEmpty()
            );
        }

        // Filter specified search terms.
        $checkedBoxes = $filterSearchBoxes->filter(fn ($box) => $box)->keys();

        if ($checkedBoxes->isNotEmpty() && $filters->get('search')->isNotEmpty()) {
            $cards = $cards->filter(
                function ($card) use ($course, $filters, $checkedBoxes) {
                    // Get each contents of the card associated to the corresponding
                    // checked boxes (name: title, box2: ICOR or text, etc.).
                    $contents = collect([
                        'name' => $card->title,
                        CardBox::Box2 => match ($course->transcrition) {
                            // Transform ICOR transcription into plain text.
                            TranscriptionType::Icor => collect([])
                                ->concat(collect($card->box2[TranscriptionType::Icor])->pluck('speaker'))
                                ->concat(collect($card->box2[TranscriptionType::Icor])->pluck('speech'))
                                ->join(''),
                            default => $card->box2[TranscriptionType::Text] ?? '',
                        },
                        CardBox::Box3 => $card->box3 ?? '',
                        CardBox::Box4 => $card->box4 ?? '',
                    ]);

                    // Get only the contents associated to the checked boxes.
                    $contents = $contents->filter(
                        fn ($value, $key) => $checkedBoxes->contains($key),
                    );

                    // Search for the search term in each contents.
                    $found = $filters
                        ->get('search')
                        ->some(fn ($searchTerm) => $contents->some(fn ($content) => static::searchTerm($content, $searchTerm)));

                    return $found;
                }
            );
        }

        // Filter cards by user permissions.
        $user = Auth::user();
        $cards = $cards->filter(fn ($card) => $user->can('viewInFinder', $card));

        $results = $cards
            ->concat(
                // Get all folders, folders are not affected by filters.
                Folder::where('course_id', $course->id)
                    ->where('parent_id', $folder?->id)
                    ->get()
            )
            ->sortBy([
                [$sortColumn, $sortDirection],
                ['id', 'asc'],
            ])
            ->values();

        return $results;
    }

    /**
     * Return the title of the folder with the path until the specified folder
     * or to the root folder.
     */
    public static function getFolderAbsolutePath(
        Folder $folder,
        Folder $until = null,
        bool $self = true,
        string $separator = ' > ',
    ): string {
        return $folder
            ->getAncestors($self, $until)
            ->reverse()
            ->pluck('title')
            ->implode($separator);
    }

    /**
     * Return a collection of folders with an added attribute "titleFullPath"
     * representing the result of the Helpers::getFolderAbsolutePath() function.
     */
    public static function getFolderListAbsolutePath(
        Collection $folders,
        Folder $until = null,
        bool $self = true,
        string $separator = ' > ',
    ): Collection {
        return $folders->map(
            function ($folder) use ($until, $self, $separator) {
                $folder->titleFullPath = static::getFolderAbsolutePath(
                    $folder, $until, $self, $separator
                );

                return $folder;
            },
        );
    }

    /**
     * Return the number of cards contained in the given folder and its
     * children recursively.
     */
    public static function countCardsRecursive(
        Folder $folder,
        Collection $filters,
        Collection $filterSearchBoxes,
        string $sortColumn = 'position',
        string $sortDirection = 'asc',
    ): int {
        $content = static::getFolderContent(
            $folder->course,
            $filters,
            $filterSearchBoxes,
            $folder,
            $sortColumn,
            $sortDirection,
        );
        $count = $content
            ->countBy(fn ($row) => $row->getFinderRowType())
            ->get(FinderRowType::Card, 0);

        foreach ($folder->children as $child) {
            $count += static::countCardsRecursive(
                $child,
                $filters,
                $filterSearchBoxes,
                $sortColumn,
                $sortDirection,
            );
        }

        return $count;
    }

    /**
     * Search $term inside $text. Case insensitive and without spaces.
     * HTML tags are stripped in $text.
     *
     * Return if the term is found or not.
     */
    private static function searchTerm(string $text, string $term): bool
    {
        return str_contains(
            strtoupper(str_replace(' ', '', strip_tags($text))),
            strtoupper(str_replace(' ', '', $term)),
        );
    }
}

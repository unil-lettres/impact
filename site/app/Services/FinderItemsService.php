<?php

namespace App\Services;

use App\Card;
use App\Course;
use App\Enums\CardBox;
use App\Enums\FinderItemType;
use App\Enums\TranscriptionType;
use App\Folder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Manage the content (cards or folders) of courses and folders with filters
 * and sort.
 */
class FinderItemsService
{
    /**
     * Return a collection of cards and folders contained inside the given
     * folder.
     *
     * Cards are filtered by given filters.
     *
     * Items are sorted by given sort column and direction.
     *
     * If no folder are given, return the root items of the course.
     *
     * $filters is a collection with the given format:
     *   [
     *      'tags' => [tag_id,...],
     *      'state' => [state_id,...],
     *      'holder' => [holder_id,...],
     *      'search' => [terms (string),...],
     *   ]
     *
     * $filterSearchBoxes is  collection with the given format:
     *   [
     *      'name' => bool,
     *      'box2' => bool,
     *      'box3' => bool,
     *      'box4' => bool,
     *   ]
     */
    public static function getItems(
        Course $course,
        Collection $filters,
        array $filterSearchBoxes,
        ?Folder $folder = null,
        string $sortColumn = 'position',
        string $sortDirection = 'asc',
    ): Collection {

        $cards = Card::with('tags')->with('state')->with('folder')->with('course')
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

        // Filter specified holders id.
        // Due to how holders are implemented, we do this directly in the
        // collection.
        if ($filters->get('holder')->isNotEmpty()) {
            $cards = $cards->filter(
                fn ($card) => $card
                    ->holders()
                    ->pluck('id')
                    ->intersect($filters->get('holder'))
                    ->isNotEmpty()
            );
        }

        // Filter specified search terms.
        $checkedBoxes = collect($filterSearchBoxes)->filter(fn ($box) => $box)->keys();

        if ($checkedBoxes->isNotEmpty() && $filters->get('search')->isNotEmpty()) {
            $cards = $cards->filter(
                function ($card) use ($course, $filters, $checkedBoxes) {
                    // Get each contents of the card associated to the corresponding
                    // checked boxes (name: title, box2: ICOR or text, etc.).
                    $contents = collect([
                        'name' => $card->title,
                        CardBox::Box2 => match ($course->transcription) {
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
                        ->some(fn ($searchTerm) => $contents->some(
                            fn ($content) => static::searchTerm($content, $searchTerm)
                        ));

                    return $found;
                }
            );
        }

        // Filter cards by user permissions.
        $user = Auth::user();
        $cards = $cards->filter(fn ($card) => $user->can('index', $card));

        $results = $cards
            ->concat(
                // Get all folders, folders are not affected by filters.
                Folder::with('course')
                    ->where('course_id', $course->id)
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
     * Return the number of cards contained in the given folder and its
     * children recursively.
     */
    public static function countCardsRecursive(
        Folder $folder,
        Collection $filters,
        array $filterSearchBoxes,
        string $sortColumn = 'position',
        string $sortDirection = 'asc',
    ): int {
        $content = static::getItems(
            $folder->course,
            $filters,
            $filterSearchBoxes,
            $folder,
            $sortColumn,
            $sortDirection,
        );
        $count = $content
            ->countBy(fn ($item) => $item->getFinderItemType())
            ->get(FinderItemType::Card, 0);

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

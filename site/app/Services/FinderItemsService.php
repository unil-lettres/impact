<?php

namespace App\Services;

use App\Card;
use App\Course;
use App\Folder;
use Illuminate\Support\Collection;

/**
 * Manage the content (cards or folders) of courses and folders with filters
 * and sort.
 *
 * This only reads the content of one folder. Rendering a folder along with its
 * descendants, as the finder does, should build a FinderTree instead, which
 * loads the course once and answers for every folder of it.
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
            ->get();

        // Get all folders, folders are not affected by filters.
        $folders = Folder::with('course')
            ->where('course_id', $course->id)
            ->where('parent_id', $folder?->id)
            ->get();

        return FinderTree::sortItems(
            FinderTree::keepListableCards(
                $course,
                $cards,
                $filters,
                $filterSearchBoxes,
            )->concat($folders),
            $sortColumn,
            $sortDirection,
        );
    }
}

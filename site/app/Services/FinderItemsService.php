<?php

namespace App\Services;

use App\Course;
use App\Folder;
use Illuminate\Support\Collection;

/**
 * Manage the content (cards or folders) of courses and folders with filters
 * and sort.
 *
 * These helpers load the content of the course on every call. When several
 * folders of the same course are needed, as when the finder renders a folder
 * and its descendants, build a FinderTree once and query it instead.
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
        return FinderTree::build(
            $course,
            $filters,
            $filterSearchBoxes,
            $sortColumn,
            $sortDirection,
        )->items($folder);
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
        return FinderTree::build(
            $folder->course,
            $filters,
            $filterSearchBoxes,
            $sortColumn,
            $sortDirection,
        )->countCardsRecursive($folder);
    }
}

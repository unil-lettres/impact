<?php

namespace App\Services\Clone;

use App\Card;
use App\Course;
use App\Folder;
use Illuminate\Support\Collection;

class MassCloneService
{
    /**
     * Return the corresponding service for the given parameter.
     */
    public static function getCloneService(
        Card|Folder $cardOrFolder,
    ): CloneCardService|CloneFolderService {
        if ($cardOrFolder instanceof Card) {
            return new CloneCardService($cardOrFolder);
        }

        return new CloneFolderService($cardOrFolder);
    }

    /**
     * Clone all cards and folders of the given collection in the destination
     * course.
     *
     * Check every items before cloning. If one item check fails, no clone are
     * performed.
     */
    public static function massCloneCardsAndFolders(
        Collection $cardsAndFolders,
        Course $dest,
    ): void {
        $cardsAndFolders
            // We must check all items before cloning any of them.
            ->each(fn ($item) => static::getCloneService($item)->checkClone(null, $dest))
            ->each(fn ($item) => static::getCloneService($item)->clone(null, $dest));
    }
}

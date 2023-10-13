<?php

namespace App\Services\Clone;

use App\Card;
use App\Course;
use App\Folder;
use Illuminate\Support\Collection;

class MassCloneService
{
    public static function getCloneService(
        Card|Folder $cardOrFolder,
    ): CloneCardService|CloneFolderService {
        if ($cardOrFolder instanceof Card) {
            return new CloneCardService($cardOrFolder);
        }

        return new CloneFolderService($cardOrFolder);
    }

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

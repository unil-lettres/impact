<?php

namespace App\Traits;

use App\Card;
use App\Folder;

trait FindLastPosition
{
    /**
     * Return the last available position for a card or folder based on existing
     * content of the parent. The position of the given card or folder will not
     * be taken into account.
     *
     * @param  $cardOrFolder  Card|Folder The card or folder to get the next position to use.
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

        // If there is no items in the parent.
        if (is_null($maxCardPosition ?? $maxFolderPosition)) {
            return 0;
        }

        $position = max($maxCardPosition, $maxFolderPosition);

        return $position + 1;
    }
}

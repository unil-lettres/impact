<?php

namespace App\Services;

use App\Card;
use App\Folder;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class MoveService
{
    /**
     * Move this card or folder to another folder of the same course.
     *
     * @param  Card|Folder  $cardOrFolder The card or folder to move.
     * @param  Folder|null  $dest The new parent folder. Null if the card or
     * folder should be moved to the root folder.
     */
    public static function moveCardOrFolder(
        Card|Folder $cardOrFolder,
        Folder $dest = null,
    ): void {
        if ($cardOrFolder instanceof Card) {
            static::moveCard($cardOrFolder, $dest);
        } else {
            static::moveFolder($cardOrFolder, $dest);
        }
    }

    /**
     * Move this folder to another folder of the same course.
     *
     * @param  Folder  $folder The folder to move.
     * @param  Folder|null  $dest The new parent folder. Null if the folder
     * should be moved to the root folder.
     *
     * @throws InvalidArgumentException If the folder is moved into a folder of
     * another course or into itself or into one of its children.
     */
    public static function moveFolder(Folder $folder, Folder $dest = null): void
    {
        if ($dest && $dest->course_id !== $folder->course_id) {
            throw new InvalidArgumentException(
                'Cannot move into a folder of another space.',
            );
        }

        if (Auth::user()->cannot('manage', $folder)) {
            abort(403);
        }

        if ($dest) {
            // Check that the folder is not moved into itself or into one of its
            // children.
            $parents = $dest->getAncestors()->pluck('id');
            if ($parents->contains($folder->id)) {
                throw new InvalidArgumentException(
                    'Cannot move a folder into itself or into one of its children',
                );
            } else {
                $folder->parent_id = $dest->id;
            }
        } else {
            $folder->parent_id = null;
        }

        $folder->save();
    }

    /**
     * Move this card to another folder.
     *
     * @param  Card  $card The card to move.
     * @param  Folder|null  $folder The new parent folder. Null if the card
     * should be moved to the root folder.
     *
     * @throws InvalidArgumentException If the card is moved into a folder of
     * another course.
     */
    public static function moveCard(Card $card, Folder $folder = null): void
    {
        if ($folder && $folder->course->id !== $card->course->id) {
            throw new InvalidArgumentException(
                'Cannot move into a folder of another space.',
            );
        }

        if (Auth::user()->cannot('manage', $card)) {
            abort(403);
        }

        $card->update(['folder_id' => $folder?->id]);
    }
}

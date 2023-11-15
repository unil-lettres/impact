<?php

namespace App\Observers;

use App\Folder;
use App\Traits\FindLastPosition;

class FolderObserver
{
    use FindLastPosition;

    /**
     * Handle the Folder "created" event.
     *
     * @return void
     */
    public function created(Folder $folder)
    {
        // Get the next position based on other cards and folders of this course.
        if (is_null($folder->position)) {
            // We update quietly since we don't want to trigger the updated
            // event to avoid recalculation of the position.
            $folder->updateQuietly([
                'position' => $this->findLastPositionInParent($folder),
            ]);
        }
    }

    /**
     * Handle the Folder "updated" event.
     */
    public function updated(Folder $folder): void
    {
        if ($folder->wasChanged('parent_id')) {
            // We update quietly to avoid recursion.
            $folder->updateQuietly([
                'position' => $this->findLastPositionInParent($folder),
            ]);
        }
    }

    /**
     * Handle the Folder "forceDeleting" event.
     */
    public function forceDeleting(Folder $folder): void
    {
        // Delete cards "manually" because they have custom forceDelete.
        $folder->cards()->each(fn ($card) => $card->forceDelete());

        // Delete children folder "manually" because they have custom forceDelete.
        $folder->children()->each(fn ($child) => $child->forceDelete());
    }
}


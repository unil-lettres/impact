<?php

namespace App\Observers;

use App\Folder;
use App\Helpers\Helpers;

class FolderObserver
{
    /**
     * Handle the Folder "created" event.
     *
     * @return void
     */
    public function created(Folder $folder)
    {
        // Get the next position based on other cards and folders of this course.
        if (is_null($folder->position)) {
            $folder->updateQuietly([
                'position' => Helpers::findLastPositionInParent($folder),
            ]);
        }
    }

    /**
     * Handle the Folder "updated" event.
     */
    public function updated(Folder $folder): void
    {
        if ($folder->wasChanged('parent_id')) {
            $folder->updateQuietly([
                'position' => Helpers::findLastPositionInParent($folder),
            ]);
        }
    }
}

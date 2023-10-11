<?php

namespace App\Services\Clone;

use App\Course;
use App\Folder;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class CloneFolderService
{
    private Folder $folder;

    public function __construct(Folder $folder)
    {
        $this->folder = $folder;
    }

    /**
     * Check if the folder can be cloned.
     *
     * Same params than CloneService::cloneFolder().
     *
     * @throws CloneException If the folder cannot be cloned.
     */
    public function checkClone(
        Folder $destFolder = null,
        Course $destCourse = null,
    ): void {
        // Check that all children can be cloned.
        $this->folder
            ->children
            ->concat($this->folder->cards)
            ->every(
                fn ($entity) => MassCloneService::getCloneService($entity)->checkClone($destFolder, $destCourse)
            );
    }

    /**
     * Clone this folder and all contained cards.
     *
     * @param  Folder|null  $destFolder The new parent folder. Null if the folder
     * should be cloned in the same parent folder.
     * @param  Course|null  $destCourse The new course. Null if the folder
     * should be cloned in the same course.
     *
     * @throws InvalidArgumentException If both $destFolder and $destCourse are
     * specified.
     */
    public function clone(
        Folder $destFolder = null,
        Course $destCourse = null,
    ) {
        $this->checkClone($destFolder, $destCourse);

        // Can specify only one of these attribute (course will be deduced from
        // folder if specified).
        if ($destFolder && $destCourse) {
            throw new InvalidArgumentException(
                'Cannot specify $destFolder and $destCourse at the same time.',
            );
        }

        if ($destCourse && $destCourse->id === $this->folder->course->id) {
            $destCourse = null;
        }

        if (Auth::user()->cannot('update', $this->folder)) {
            abort(403);
        }

        if ($destFolder) {
            if (!Auth::user()->isTeacher($destFolder->course)) {
                abort(403);
            }

            $values = [
                'parent_id' => $destFolder->id,
                'course_id' => $destFolder->course_id,
            ];
        } elseif ($destCourse) {
            if (!Auth::user()->isTeacher($destCourse)) {
                abort(403);
            }

            $values = [
                'parent_id' => null,
                'course_id' => $destCourse->id,
            ];
        } else {
            $copyLabel = trans('courses.finder.copy');
            $values = [
                'title' => "{$this->folder->title} ($copyLabel)",
            ];
        }
        $copiedFolder = $this->folder->replicate(['position'])->fill($values);
        $copiedFolder->save();
        $copiedFolder->refresh();

        // Clone children (folder and cards).
        $this->folder->children
            ->concat($this->folder->cards)
            ->sortBy('position')
            ->each(fn ($entity) => MassCloneService::getCloneService($entity)->clone($copiedFolder));
    }
}

<?php

namespace App;

use App\Enums\FinderRowType;
use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Folder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'position', 'course_id', 'parent_id',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the course of this folder.
     */
    public function course()
    {
        return $this->hasOne('App\Course', 'id', 'course_id');
    }

    /**
     * Get the parent of this folder.
     */
    public function parent()
    {
        return $this->hasOne('App\Folder', 'id', 'parent_id');
    }

    /**
     * Get the children of this folder.
     */
    public function children()
    {
        return $this->hasMany('App\Folder', 'parent_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the cards of this folder.
     */
    public function cards()
    {
        return $this->hasMany('App\Card', 'folder_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the breadcrumbs for this folder
     *
     * Define if the breadcrumbs should contain the current folder
     *
     * @param  bool  $self
     *
     * This function will return a Collection and should contain
     * a path as the key, and a name as the value.
     * @return Collection
     */
    public function breadcrumbs(bool $self = false)
    {
        $breadcrumbs = $this->course
            ->breadcrumbs(true);

        if ($this->parent()->get()->isNotEmpty()) {
            // Iterate through hierarchical parents while a parent exists
            $parent = $this->parent();
            while ($parent->get()->isNotEmpty()) {
                $breadcrumbs->put(
                    route('folders.show', $parent->first()->id), $parent->first()->title
                );
                $parent = $parent->first()->parent();
            }
        }

        if ($self) {
            // Add the current folder to the breadcrumbs
            $breadcrumbs->put(
                route('folders.show', $this->id), $this->title
            );
        }

        return $breadcrumbs;
    }

    public function getType(): string
    {
        return FinderRowType::Folder;
    }

    public function getContent(
        string $sortColumn = 'position',
        string $sortDirection = 'asc',
        Collection $filters = null,
    ): Collection {
        return Helpers::getFolderContent(
            $this->course,
            $filters,
            $this,
            $sortColumn,
            $sortDirection,
        );
    }

    /**
     * Clone this folder and all contained cards.
     *
     * @param  Folder|null  $destFolder The new parent folder. Null if the folder
     * should be cloned in the same parent folder.
     * @param  Course|null  $destCourse The new course. Null if the folder
     * should be cloned in the same course.
     */
    public function clone($destFolder = null, $destCourse = null)
    {
        // Can specify only one of these attribute (course will be deduced from
        // folder if specified).
        if ($destFolder && $destCourse) {
            // TODO throw error
            return;
        }
        if ($destFolder) {
            $values = [
                'parent_id' => $destFolder->id,
                'course_id' => $destFolder->course_id,
            ];
        } elseif ($destCourse) {
            $values = [
                'parent_id' => null,
                'course_id' => $destCourse->id,
            ];
        } else {
            $copyLabel = trans('courses.finder.copy');
            $values = [
                'title' => "{$this->title} ($copyLabel)",
            ];
        }
        DB::transaction(function () use ($values) {
            $copiedFolder = $this->replicate(['position'])->fill($values);
            $copiedFolder->save();

            // Clone children (folder and cards).
            $this->children
                ->concat($this->cards)
                ->sortBy('position')
                ->each(fn ($entity) => $entity->clone($copiedFolder));
        });
    }

    /**
     * Move this folder to another folder of the same course.
     *
     * @param  Folder|null  $folder The new parent folder. Null if the folder
     * should be moved to the root folder.
     */
    public function move(Folder $folder = null)
    {
        if ($folder && $folder->course_id !== $this->course_id) {
            // TODO throw error
            return;
        }

        if ($folder) {
            // Check that the folder is not moved into itself or into one of its
            // children.
            $parents = $folder->getAllParents()->push($folder)->pluck('id');
            if ($parents->contains($this->id)) {
                // TODO throw error
                return;
            } else {
                $this->parent_id = $folder->id;
            }
        } else {
            $this->parent_id = null;
        }

        $this->save();
    }

    /**
     * Get all parents of this folder.
     */
    public function getAllParents(): Collection
    {
        $parents = collect([]);

        $parent = $this->parent;
        while ($parent) {
            $parents->push($parent);
            $parent = $parent->parent;
        }

        return $parents;
    }
}

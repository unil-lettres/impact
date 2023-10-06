<?php

namespace App;

use App\Enums\FinderRowType;
use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Folder extends Model
{
    use SoftDeletes {
        forceDelete as traitForceDelete;
    }

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
        $breadcrumbs = collect([]);

        if ($self) {
            // Add the current folder to the breadcrumbs
            $breadcrumbs->put(
                route('folders.show', $this->id), $this->title
            );
        }

        if ($this->parent()->get()->isNotEmpty()) {
            // Iterate through hierarchical parents while a parent exists
            $parent = $this->parent();
            while ($parent->get()->isNotEmpty()) {
                $breadcrumbs->put(
                    route('folders.show', $parent->first()->id),
                    $parent->first()->title,
                );
                $parent = $parent->first()->parent();
            }
        }

        return $this->course->breadcrumbs(true)->merge($breadcrumbs->reverse());
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

    public function forceDelete()
    {
        // Delete cards "manually" because they have custom forceDelete.
        $this->cards()->each(fn ($card) => $card->forceDelete());

        // Delete children folder "manually" because they have custom forceDelete.
        $this->children()->each(fn ($child) => $child->forceDelete());

        // Delete the folder.
        $this->traitForceDelete();
    }

    /**
     * Get all ancestors of this folder.
     *
     * @param  bool  $self  If true, the current folder will be included in the
     * ancestors.
     * @param  Folder|null  $until  If set, the ancestors will be returned until
     * this folder.
     */
    public function getAncestors(bool $self = true, Folder $until = null): Collection
    {
        $parents = collect([]);

        if ($self) {
            $parents->push($this);
        }

        $parent = $this->parent;
        while ($parent && (! $until || $parent->id !== $until->id)) {
            $parents->push($parent);
            $parent = $parent->parent;
        }

        return $parents;
    }

    /**
     * Get all children of this folder and children of children recursively.
     */
    public function getChildrenRecursive(): Collection
    {
        $children = $this->children;

        foreach ($this->children as $child) {
            $children = $children->merge($child->getChildrenRecursive());
        }

        return $children;
    }
}

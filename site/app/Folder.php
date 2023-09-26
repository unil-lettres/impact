<?php

namespace App;

use App\Enums\FinderRowType;
use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
     * Duplicate this folder and all contained cards.
     *
     * @param  Folder|null  $destFolder The new parent folder. Null if the folder
     * should be duplicated in the same parent folder.
     */
    public function copy($destFolder = null)
    {
        DB::transaction(function () use ($destFolder) {
            if ($destFolder) {
                $values = [
                    'title' => $this->title,
                    'parent_id' => $destFolder->id,
                ];
            } else {
                $copyLabel = trans('courses.finder.copy');
                $values = [
                    'title' => "{$this->title} ($copyLabel)",
                    'parent_id' => $this->parent_id,
                ];
            }
            $copiedFolder = $this->replicate(['position'])->fill($values);
            $copiedFolder->save();

            // Copy children.
            $this->children->each(fn ($child) => $child->copy($copiedFolder));
            $this->cards->each(fn ($card) => $card->copy($copiedFolder));
        });
    }
}

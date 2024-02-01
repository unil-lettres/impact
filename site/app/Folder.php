<?php

namespace App;

use App\Enums\FinderItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Folder extends Model
{
    use HasFactory;
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
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the parent of this folder.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    /**
     * Get the children of this folder.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the cards of this folder.
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the breadcrumbs for this folder.
     *
     * The "self" parameter defines if the breadcrumbs should
     * include the current folder or not.
     *
     * This function will return a Collection and should contain
     * a path as the key, and a name as the value.
     */
    public function breadcrumbs(bool $self = false): Collection
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

    /**
     * Return the FinderItemType corresponding to the folder.
     */
    public function getFinderItemType(): string
    {
        return FinderItemType::Folder;
    }

    /**
     * Get all ancestors of this folder (all parents recursively).
     *
     * @param  bool  $self  If true, the current folder will be included in the
     * ancestors.
     * @param  Folder|null  $until  If set, the ancestors will be returned until
     * this folder.
     */
    public function getAncestors(
        bool $self = true,
        ?Folder $until = null,
    ): Collection {
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


    /**
     * Get all cards of this folder and cards of children recursively.
     */
    public function getCardsRecursive(): Collection
    {
        $cards = $this->cards;

        foreach ($this->children as $child) {
            $cards = $cards->merge($child->getCardsRecursive());
        }

        return $cards;
    }
}

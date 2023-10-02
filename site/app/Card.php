<?php

namespace App;

use App\Enums\FinderRowType;
use App\Enums\StatePermission;
use App\Scopes\HideAttachmentsScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Card extends Model
{
    use SoftDeletes {
        forceDelete as traitForceDelete;
    }
    use HasFactory;

    const TRANSCRIPTION = '{
            "version": 1,
            "icor": [],
            "text": null
        }';

    const OPTIONS = '{
            "version": 1,
            "no_emails": false,
            "presentation_date": null,
            "box1": {
                "hidden": false,
                "link": null,
                "start": null,
                "end": null
            },
            "box2": {
                "hidden": false,
                "sync": true
            },
            "box3": {
                "hidden": false,
                "title": "Théorie",
                "fixed": false
            },
            "box4": {
                "hidden": false,
                "title": "Exemplification",
                "fixed": false
            },
            "box5": {
                "hidden": false
            }
        }';

    protected $fillable = [
        'title',
        'box2',
        'box3',
        'box4',
        'course_id',
        'state_id',
        'folder_id',
        'file_id',
        'options',
        'position',
    ];

    protected $casts = [
        'box2' => 'array',
        'options' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'box2' => self::TRANSCRIPTION,
        'options' => self::OPTIONS,
    ];

    /**
     * Get the course of this card.
     */
    public function course(): HasOne
    {
        return $this->hasOne('App\Course', 'id', 'course_id');
    }

    /**
     * Get the folder of this card.
     */
    public function folder(): HasOne
    {
        return $this->hasOne('App\Folder', 'id', 'folder_id');
    }

    /**
     * Get the file of this card.
     */
    public function file(): HasOne
    {
        return $this->hasOne('App\File', 'id', 'file_id');
    }

    /**
     * Get the state of this card.
     */
    public function state(): HasOne
    {
        return $this->hasOne('App\State', 'id', 'state_id');
    }

    /**
     * The tag that belong to the card.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderBy('name');
    }

    /**
     * Get the attachments of this card.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany('App\File', 'card_id')
            ->withoutGlobalScope(HideAttachmentsScope::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Return a collection of enrollements that have this card.
     */
    public function enrollments(): Collection
    {
        return $this->course->enrollments()->get()
            ->filter(function ($enrollment) {
                return $enrollment->cards ? in_array($this->id, $enrollment->cards) : false;
            });
    }

    /**
     * Get the editors of this card.
     */
    public function editors(): Collection
    {
        return $this->enrollments()->map(function ($enrollment) {
            return $enrollment->user;
        })->sortBy('name');
    }

    /**
     * Get a string of all editors joined by a comma (',').
     */
    protected function editorsList(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->editors()->pluck('name')->join(', '),
        );
    }

    /**
     * Get the name of the state.
     */
    protected function stateName(): Attribute
    {
        return Attribute::make(get: fn () => $this->state->name);
    }

    /**
     * Get a string of all tags joined by a comma (',').
     */
    protected function tagsList(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tags()->pluck('name')->join(', '),
        );
    }

    /**
     * Get the breadcrumbs for this card
     *
     * Define if the breadcrumbs should contain the current card
     *
     * @param  bool  $self
     *
     * This function will return a Collection and should contain
     * a path as the key, and a name as the value.
     * @return \Illuminate\Support\Collection
     */
    public function breadcrumbs(bool $self = false)
    {
        if ($this->folder()->get()->isEmpty()) {
            // If the card has no folder, only return the course breadcrumbs
            $breadcrumbs = $this->course
                ->breadcrumbs(true);
        } else {
            // If the card has a folder, return the folder breadcrumbs
            $breadcrumbs = $this
                ->folder()
                ->first()
                ->breadcrumbs(true);
        }

        if ($self) {
            // Add the current card to the breadcrumbs
            $breadcrumbs->put(
                route('cards.show', $this->id), $this->title
            );
        }

        return $breadcrumbs;
    }

    /**
     * Return whether a box can be viewed by the current user.
     * The current state of the card is used to determine the visibility.
     */
    public function boxIsVisible(string $box): bool
    {
        if (! $this->state) {
            return false;
        }

        if (! $this->state->getPermission($box)) {
            return false;
        }

        // Check if user role is allowed to see the box
        return match ($this->state->getPermission($box)) {
            StatePermission::TeachersCanShowAndEditEditorsCanShow => Auth::user()->isTeacher($this->course) || Auth::user()->isEditor($this),
            StatePermission::EditorsCanShowAndEdit => Auth::user()->isEditor($this),
            StatePermission::TeachersAndEditorsCanShowAndEdit => Auth::user()->isTeacher($this->course) || Auth::user()->isEditor($this),
            StatePermission::AllCanShowTeachersAndEditorsCanEdit, StatePermission::AllCanShowTeachersCanEdit => Auth::user()->isTeacher($this->course) || Auth::user()->isEditor($this) || Auth::user()->isStudent($this->course),
            StatePermission::TeachersCanShowAndEdit => Auth::user()->isTeacher($this->course),
            default => Auth::user()->admin,
        };
    }

    /**
     * Return whether a box can be edited by the current user.
     * The current state of the card is used to determine the editability.
     */
    public function boxIsEditable(string $box): bool
    {
        if (! $this->state) {
            return false;
        }

        if (! $this->state->getPermission($box)) {
            return false;
        }

        // Check if user role is allowed to edit the box
        return match ($this->state->getPermission($box)) {
            StatePermission::TeachersCanShowAndEditEditorsCanShow => Auth::user()->isTeacher($this->course),
            StatePermission::EditorsCanShowAndEdit => Auth::user()->isEditor($this),
            StatePermission::TeachersAndEditorsCanShowAndEdit, StatePermission::AllCanShowTeachersAndEditorsCanEdit => Auth::user()->isTeacher($this->course) || Auth::user()->isEditor($this),
            StatePermission::AllCanShowTeachersCanEdit, StatePermission::TeachersCanShowAndEdit => Auth::user()->isTeacher($this->course),
            default => Auth::user()->admin,
        };
    }

    public function forceDelete()
    {
        // Delete attachments (only attachments and not regular file).
        $this->attachments()->each(
            fn ($attachment) => $attachment->forceDelete(),
        );

        // Remove this card from all enrollments as editors.
        $this->enrollments()->each(function ($enrollment) {
            $enrollment->removeCard($this);
        });

        // Delete the card.
        $this->traitForceDelete();
    }

    public function getType(): string
    {
        return FinderRowType::Card;
    }

    /**
     * Clone this card.
     *
     * All attachments will be cloned as well (but not regular file).
     *
     * When cloned in a new course, all files and tags will be cloned.
     *
     * @param  Folder|null  $destFolder The new parent folder. Null if the card
     * should be cloned in the same parent folder.
     * @param  Course|null  $course The new course. Null if the card should be
     * cloned in the same course.
     *
     * @throws InvalidArgumentException If both $destFolder and $destCourse are
     * specified.
     */
    public function clone(Folder $destFolder = null, Course $destCourse = null)
    {
        // Can specify only one of these attribute (course will be deduced from
        // folder if specified).
        if ($destFolder && $destCourse) {
            throw new InvalidArgumentException(
                'Cannot specify $destFolder and $destCourse at the same time.',
            );
        }

        if ($destCourse && $destCourse->id === $this->course->id) {
            $destCourse = null;
        }

        DB::beginTransaction();
        $values = [];
        if ($destCourse) {
            $values = [
                'course_id' => $destCourse->id,
                'folder_id' => null,
            ];
        } elseif ($destFolder) {
            $values = [
                'course_id' => $destFolder->course->id,
                'folder_id' => $destFolder->id,
            ];
        } else {
            $copyLabel = trans('courses.finder.copy');
            $values = [
                'title' => "{$this->title} ($copyLabel)",
            ];
        }
        $copiedCard = $this->replicate(['position'])->fill($values);
        $copiedCard->save();
        $copiedCard->refresh();
        $destCourse = $copiedCard->course;

        $failed = false;
        $files = collect([]);

        // Is the card copied in another course?
        if ($copiedCard->course->id !== $this->course->id) {
            $existingNamesInDest = $destCourse->tags()->pluck('name');

            // Create tags that don't already exists in the destination course
            // and attach them to the card.
            $copiedCard->tags()->createMany(
                collect($this->tags->toArray())
                    ->filter(fn ($tag) => ! $existingNamesInDest->contains($tag['name']))
                    ->map(
                        function ($tag) use ($destCourse) {
                            $tag['course_id'] = $destCourse->id;

                            return $tag;
                        },
                    )
                    ->toArray(),
            );

            // Attach tags that already exists in the destination course to the
            // card.
            $destCourse
                ->tags
                ->filter(fn ($tag) => $existingNamesInDest->contains($tag->name))
                ->each(fn ($tag) => $tag->cards()->attach($copiedCard->id));

            // Copy source file.
            static $alreadyCopiedFiles = [];
            if (array_key_exists($this->file->id, $alreadyCopiedFiles)) {
                // Only copy source file once. Avoid having multiple copies of
                // sources files when cloning multiple cards that have the same
                // source file. Can still happen when cloning files from
                // multiple requests.
                $copiedCard->file_id = $alreadyCopiedFiles[$this->file->id];
                $copiedCard->save();
            } else {
                $copiedSourceFile = $this->file->clone($copiedCard->id);
                if ($copiedSourceFile) {
                    $files->push($copiedSourceFile);
                    $copiedSourceFile->course_id = $copiedCard->course->id;
                    $copiedSourceFile->save();
                    $copiedCard->file_id = $copiedSourceFile->id;
                    $copiedCard->save();

                    $alreadyCopiedFiles[$this->file->id] = $copiedSourceFile->id;
                } else {
                    $failed = true;
                }
            }
        } else {
            // Attach tags.
            $copiedCard->tags()->attach($this->tags->pluck('id'));

            // Attach editors.
            $this->enrollments()->each(
                fn ($enrollment) => $enrollment->addCard($copiedCard),
            );
        }

        // Clone attachments.
        $this->attachments()->each(
            function ($attachment) use ($copiedCard, $files, $failed) {
                if ($failed) {
                    return;
                }

                $copiedFile = $attachment->clone($copiedCard->id);
                if ($copiedFile) {
                    $files->push($copiedFile);
                    $copiedFile->card_id = $copiedCard->id;
                    $copiedFile->course_id = $copiedCard->course->id;
                    $copiedFile->save();
                } else {
                    $failed = true;
                }
            }
        );

        if ($failed) {
            $files->each(fn ($file) => $file->forceDelete());
            DB::rollBack();
        } else {
            DB::commit();
        }
    }

    /**
     * Move this card to another folder.
     *
     * @param  Folder|null  $folder The new parent folder. Null if the card
     * should be moved to the root folder.
     *
     * @throws InvalidArgumentException If the card is moved into a folder of
     * another course.
     */
    public function move(Folder $folder = null): void
    {
        if ($folder && $folder->course->id !== $this->course->id) {
            throw new InvalidArgumentException(
                'Cannot move into a folder of another space.',
            );
        }
        $this->update(['folder_id' => $folder?->id]);
    }

    /**
     * Get all ancestors of this card.
     */
    public function getAncestors(): \Illuminate\Support\Collection
    {
        $parents = collect([]);

        $parent = $this->folder;
        while ($parent) {
            $parents->push($parent);
            $parent = $parent->folder;
        }

        return $parents;
    }
}

<?php

namespace App;

use App\Enums\CardBox;
use App\Enums\FinderItemType;
use App\Enums\StatePermission;
use App\Enums\StateType;
use App\Enums\TranscriptionType;
use App\Scopes\HideAttachmentsScope;
use App\Scopes\ValidityScope;
use App\Traits\IsLegacy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Card extends Model
{
    use HasFactory;
    use IsLegacy;
    use SoftDeletes;

    const MAX_CHARACTERS_SPEECH = 55;

    const MAX_CHARACTERS_LEGACY_SPEECH = 65;

    const TRANSCRIPTION = '{
            "version": 2,
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
        'box2->version',
        'box2->icor',
        'box2->text',
        'box3',
        'box4',
        'course_id',
        'state_id',
        'folder_id',
        'file_id',
        'options',
        'position',
        'options->no_emails',
        'options->presentation_date',
        'options->box1->end',
        'options->box1->hidden',
        'options->box1->link',
        'options->box1->start',
        'options->box2->hidden',
        'options->box2->sync',
        'options->box3->fixed',
        'options->box3->hidden',
        'options->box3->title',
        'options->box4->fixed',
        'options->box4->hidden',
        'options->box4->title',
        'options->box5->hidden',
        'legacy_id',
    ];

    protected $attributes = [
        'box2' => self::TRANSCRIPTION,
        'options' => self::OPTIONS,
    ];

    protected function casts(): array
    {
        return [
            'box2' => 'array',
            'options' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the course of this card.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the folder of this card.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the file of this card (regular).
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the state of this card.
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * The tag that belong to the card.
     */
    public function tags(): BelongsToMany
    {
        return $this
            ->belongsToMany(Tag::class)
            ->withPivot([
                'created_at AS pivot_created_at',
                'id AS pivot_id',
            ])
            ->withTimestamps()
            ->orderBy('pivot_created_at')

            // Can't use fraction (microseconds) for timestamp. If the timestamp
            // is the same (can happen with migrated data), we rely on the id
            // instead.
            ->orderBy('pivot_id');
    }

    /**
     * Get the attachments of this card.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(File::class)
            ->withoutGlobalScope(HideAttachmentsScope::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Return a collection of enrollments containing this card.
     * If withInvalidUsers is true, also return the enrollments that have invalid users.
     * If withUsers is true, eager load the user relation.
     */
    public function enrollments(bool $withInvalidUsers = false, bool $withUsers = false): Collection
    {
        static $cachedEnrollments = [];

        $key = md5(serialize([$this->course->id, $withInvalidUsers, $withUsers]));
        if (! isset($cachedEnrollments[$key])) {

            $enrollments = match ($withInvalidUsers) {
                true => $this->course->enrollments()
                    ->withoutGlobalScope(ValidityScope::class),
                default => $this->course->enrollments(),
            };

            if ($withUsers) {
                $enrollments->with('user');
            }

            $cachedEnrollments[$key] = $enrollments->get();
        }

        return $cachedEnrollments[$key]->filter(function ($enrollment) {
            return $enrollment->hasCard($this);
        });
    }

    /**
     * Get the holders of this card.
     */
    public function holders(): Collection
    {
        return $this->enrollments(withUsers: true)
            ->map(function ($enrollment) {
                return $enrollment->user;
            })
            ->sortBy('name');
    }

    /**
     * Check if a user can be removed from the holders of this card.
     */
    public function canRemoveHolder(User $user): bool
    {
        // Check if the user is an holder of the card
        if (! $user->isHolder($this)) {
            return false;
        }

        // Admins can remove any holder
        if (Auth::user()->admin) {
            return true;
        }

        // Check if the card state is set to private and
        // if the user is the last holder of the card
        if ($this->state->type === StateType::Private &&
            $this->holders()->where('id', '!=', $user->id)->isEmpty()) {
            return false;
        }

        return true;
    }

    /**
     * Get a string of all holders joined by a comma (',').
     * This attribute allow to sort by holders within a Collection.
     */
    protected function holdersList(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->holders()->pluck('name')->join(', '),
        );
    }

    /**
     * Get the name of the state.
     * This attribute allow to sort by state name within a Collection.
     */
    protected function stateName(): Attribute
    {
        return Attribute::make(get: fn () => $this->state->name);
    }

    /**
     * Get a string of all tags joined by a comma (',').
     * This attribute allow to sort by tags within a Collection.
     */
    protected function tagsList(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tags->pluck('name')->join(', '),
        );
    }

    /**
     * Get the breadcrumbs for this card.
     *
     * The "self" parameter defines if the breadcrumbs should
     * include the current card or not.
     *
     * This function will return a Collection and should contain
     * a path as the key, and a name as the value.
     */
    public function breadcrumbs(bool $self = false): Collection
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

        // If the option hidden is set on a box, don't show it to anyone but managers and holders.
        if (! (Auth::user()->isManager($this->course) || Auth::user()->isHolder($this)) && ($this->options[$box]['hidden'] ?? false)) {
            return false;
        }

        // Check if user role is allowed to see the box
        return match ($this->state->getPermission($box)) {
            StatePermission::HoldersCanShowAndEdit => Auth::user()->isHolder($this),
            StatePermission::ManagersAndHoldersCanShowAndEdit => Auth::user()->isManager($this->course) || Auth::user()->isHolder($this),
            StatePermission::AllCanShowManagersAndHoldersCanEdit, StatePermission::AllCanShowManagersCanEdit => Auth::user()->isManager($this->course) || Auth::user()->isHolder($this) || Auth::user()->isMember($this->course),
            StatePermission::ManagersCanShowAndEdit => Auth::user()->isManager($this->course),
            default => Auth::user()->admin,
        };
    }

    /**
     * Return whether the user can't see any box due to state permission.
     */
    public function allBoxesAreHidden(): bool
    {
        return CardBox::getAllBoxes()
            ->every(fn ($box) => ! $this->boxIsVisible($box));
    }

    /**
     * Return a collection of boxes for this card.
     * Can be limited to a specific set of boxes.
     */
    public function getBoxes(?array $boxes = null): Collection
    {
        $boxes = $boxes ? collect($boxes) : CardBox::getAllBoxes();

        return collect($boxes)
            ->map(function ($box) use ($boxes) {
                if ($boxes && ! $boxes->contains($box)) {
                    return null;
                }

                return [
                    'name' => $box,
                    'content' => $this->{$box},
                ];
            })
            ->filter();
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
            StatePermission::HoldersCanShowAndEdit => Auth::user()->isHolder($this),
            StatePermission::ManagersAndHoldersCanShowAndEdit, StatePermission::AllCanShowManagersAndHoldersCanEdit => Auth::user()->isManager($this->course) || Auth::user()->isHolder($this),
            StatePermission::AllCanShowManagersCanEdit, StatePermission::ManagersCanShowAndEdit => Auth::user()->isManager($this->course),
            default => Auth::user()->admin,
        };
    }

    /**
     * Return the FinderItemType corresponding to the card.
     */
    public function getFinderItemType(): string
    {
        return FinderItemType::Card;
    }

    /**
     * Get all ancestors of this card (all parents recursively).
     */
    public function getAncestors(): Collection
    {
        $parents = collect([]);

        $parent = $this->folder;
        while ($parent) {
            $parents->push($parent);
            $parent = $parent->folder;
        }

        return $parents;
    }

    /**
     * Return the max number of characters by line for the transcription box.
     * If the transcription box is not ICOR, return null.
     */
    public function getMaxCharactersByLine(): ?int
    {
        if ($this->course->transcription === TranscriptionType::Icor) {
            return
                (int) $this->box2['version'] > 1
                ? static::MAX_CHARACTERS_SPEECH
                : static::MAX_CHARACTERS_LEGACY_SPEECH;
        }

        return null;
    }
}

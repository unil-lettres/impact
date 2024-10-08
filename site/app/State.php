<?php

namespace App;

use App\Enums\ActionType;
use App\Enums\StatePermission;
use App\Enums\StateType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class State extends Model implements Sortable
{
    use HasFactory;
    use SoftDeletes;
    use SortableTrait;

    const PERMISSIONS = '{
            "version": 1,
            "box1": '.StatePermission::HoldersCanShowAndEdit.',
            "box2": '.StatePermission::HoldersCanShowAndEdit.',
            "box3": '.StatePermission::HoldersCanShowAndEdit.',
            "box4": '.StatePermission::HoldersCanShowAndEdit.',
            "box5": '.StatePermission::HoldersCanShowAndEdit.'
        }';

    const ACTIONS = '{
            "version": 1,
            "data": []
        }';

    protected $fillable = [
        'name',
        'description',
        'position',
        'permissions',
        'permissions->box1',
        'permissions->box2',
        'permissions->box3',
        'permissions->box4',
        'permissions->box5',
        'course_id',
        'type',
        'managers_only',
        'actions',
        'actions->data',
    ];

    protected $attributes = [
        'permissions' => self::PERMISSIONS,
        'actions' => self::ACTIONS,
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'actions' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to remove the states that are only available for managers.
     */
    public function scopeLimited(Builder $query, Card $card): void
    {
        $query->where('managers_only', false)
            ->where('type', '!=', StateType::Archived);

        if ($card->state) {
            $query->where('position', '>=', $card->state->position);
        }
    }

    /**
     * Get the course of this card.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the cards of this state.
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Update the permission of a specific box
     *
     * @param  int  $permission  (App\Enums\StatePermission)
     */
    public function updatePermission(string $box, int $permission): void
    {
        $permissions = $this->permissions;
        $permissions[$box] = $permission;

        $this->update([
            'permissions' => $permissions,
        ]);
    }

    /**
     * Get the permission of a specific box
     */
    public function getPermission(string $box): ?int
    {
        $permissions = $this->permissions;

        if (! $permissions) {
            return null;
        }

        if (! isset($permissions[$box])) {
            return null;
        }

        return $permissions[$box];
    }

    /**
     * Update the permission of all boxes
     *
     * @param  int  $permission  (App\Enums\StatePermission)
     */
    public function updatePermissions(int $permission): void
    {
        $permissions = $this->permissions;
        $permissions['box1'] = $permission;
        $permissions['box2'] = $permission;
        $permissions['box3'] = $permission;
        $permissions['box4'] = $permission;
        $permissions['box5'] = $permission;

        $this->update([
            'permissions' => $permissions,
        ]);
    }

    /**
     * Get all the boxes with a public permission (AllCanShow)
     */
    public function publicPermissions(): array
    {
        if (! $this->permissions) {
            return [];
        }

        return collect($this->permissions)
            ->filter(function ($permission) {
                return State::isPermissionPublic($permission);
            })->keys()->toArray();
    }

    /**
     * Check whether state has at least one public permission
     */
    public function hasPublicPermission(): bool
    {
        return ! empty($this->publicPermissions());
    }

    /**
     * Get all actions for this state, or only for a specific type
     *
     * @param  string|null  $type  (App\Enums\ActionType)
     */
    public function getActionsData(?string $type = null): array
    {
        if (! $this->actions) {
            return [];
        }

        if (! isset($this->actions['data'])) {
            return [];
        }

        if ($type) {
            // Keep only the actions of the given type
            return collect($this->actions['data'])
                ->filter(function ($action) use ($type) {
                    return isset($action['type']) && $action['type'] === $type;
                })->toArray();
        }

        return $this->actions['data'];
    }

    /**
     * Get the data of a specific action if available
     *
     * @param  string|null  $type  (App\Enums\ActionType)
     */
    public function getActionData(int $index, ?string $type = null): ?array
    {
        $actions = $this->getActionsData($type);

        if (isset($actions[$index])) {
            return $actions[$index];
        }

        return null;
    }

    /**
     * Build email action structure
     */
    public static function buildEmailAction(string $subject, string $message): array
    {
        return [
            'type' => ActionType::Email,
            'subject' => $subject,
            'message' => $message,
        ];
    }

    /**
     * Check whether the permission is considered public
     */
    public static function isPermissionPublic(int $permission): bool
    {
        return $permission === StatePermission::AllCanShowManagersAndHoldersCanEdit ||
            $permission === StatePermission::AllCanShowManagersCanEdit;
    }
}

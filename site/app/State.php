<?php

namespace App;

use App\Enums\StatePermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class State extends Model implements Sortable
{
    use SoftDeletes;
    use SortableTrait;
    use HasFactory;

    const PERMISSIONS = '{
            "version": 1,
            "box1": '.StatePermission::EditorsCanShowAndEdit.',
            "box2": '.StatePermission::EditorsCanShowAndEdit.',
            "box3": '.StatePermission::EditorsCanShowAndEdit.',
            "box4": '.StatePermission::EditorsCanShowAndEdit.',
            "box5": '.StatePermission::EditorsCanShowAndEdit.'
        }';

    const ACTIONS = '{
            "version": 1,
            "data": []
        }';

    protected $fillable = [
        'name', 'description', 'position', 'permissions', 'course_id', 'type', 'teachers_only', 'actions',
    ];

    protected $casts = [
        'permissions' => 'array',
        'actions' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'permissions' => self::PERMISSIONS,
        'actions' => self::ACTIONS,
    ];

    /**
     * Get the course of this card.
     */
    public function course()
    {
        return $this->hasOne('App\Course', 'id', 'course_id');
    }

    /**
     * Get the cards of this state.
     */
    public function cards()
    {
        return $this->hasMany('App\Card', 'state_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Update the permission of a specific box
     *
     * @param  int  $permission (App\Enums\StatePermission)
     */
    public function updatePermission(string $box, int $permission)
    {
        $permissions = $this->permissions;
        $permissions[$box] = $permission;

        $this->update([
            'permissions' => $permissions,
        ]);
        $this->save();
    }

    /**
     * Update the permission of all boxes
     *
     * @param  int  $permission (App\Enums\StatePermission)
     */
    public function updatePermissions(int $permission)
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
        $this->save();
    }

    /**
     * Get all actions for this state, or only for a specific type
     *
     * @param  string|null  $type (App\Enums\ActionType)
     * @return array
     */
    public function getActionsData(string $type = null)
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
     * @param  string|null  $type (App\Enums\ActionType)
     * @return array|null
     */
    public function getActionData(int $index, string $type = null)
    {
        $actions = $this->getActionsData($type);

        if (isset($actions[$index])) {
            return $actions[$index];
        }

        return null;
    }
}

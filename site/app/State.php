<?php

namespace App;

use App\Enums\StatePermission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class State extends Model implements Sortable
{
    use SoftDeletes;
    use SortableTrait;

    const PERMISSIONS = '{
            "version": 1,
            "box1": '.StatePermission::EditorsCanShowAndEdit.',
            "box2": '.StatePermission::EditorsCanShowAndEdit.',
            "box3": '.StatePermission::EditorsCanShowAndEdit.',
            "box4": '.StatePermission::EditorsCanShowAndEdit.',
            "box5": '.StatePermission::EditorsCanShowAndEdit.'
        }';

    protected $fillable = [
        'name', 'description', 'position', 'permissions', 'course_id', 'type', 'teachers_only',
    ];

    protected $dates = [
        'deleted_at',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    protected $attributes = [
        'permissions' => self::PERMISSIONS,
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
     * @param string $box
     * @param int $permission (App\Enums\StatePermission)
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
     * @param int $permission (App\Enums\StatePermission)
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
}

<?php

namespace App;

use App\Enums\StatePermission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use SoftDeletes;

    const PERMISSIONS = '{
            "version": 1,
            "teachers_only": false,
            "box1": '. StatePermission::EditorsCanShowAndEdit .',
            "box2": '. StatePermission::EditorsCanShowAndEdit .',
            "box3": '. StatePermission::EditorsCanShowAndEdit .',
            "box4": '. StatePermission::EditorsCanShowAndEdit .',
            "box5": '. StatePermission::EditorsCanShowAndEdit .'
        }';

    protected $fillable = [
        'name', 'description', 'position', 'permissions'
    ];

    protected $dates = [
        'deleted_at'
    ];

    protected $casts = [
        'permissions' => 'array'
    ];

    protected $attributes = [
        'permissions' => State::PERMISSIONS
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
}
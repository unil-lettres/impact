<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $fillable = [
        'title', 'position', 'course_id', 'parent_id'
    ];

    /**
     * Get the course of this folder.
     */
    public function course()
    {
        return $this->hasOne('App\Course', 'id', 'course_id')
            ->withTrashed();
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
     * Check whether the current folder is part of an active course or not
     *
     * @return boolean
     */
    public function isActive() {
        return $this->course->trashed() ? false : true;
    }
}

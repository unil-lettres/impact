<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'filename', 'status', 'type', 'size', 'width', 'height', 'length', 'course_id',
    ];

    /**
     * Get the course of this file.
     */
    public function course()
    {
        return $this->hasOne('App\Course', 'id', 'course_id')
            ->withTrashed();
    }

    /**
     * Get the cards of this file.
     */
    public function cards()
    {
        return $this->hasMany('App\Card', 'file_id')
            ->orderBy('created_at', 'desc');
    }
}

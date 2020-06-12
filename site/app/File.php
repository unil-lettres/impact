<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'filename', 'status', 'type', 'size', 'width', 'height', 'length', 'course_id',
    ];

    protected $dates = [
        'deleted_at'
    ];

    /**
     * Get the course of this file.
     */
    public function course()
    {
        return $this->hasOne('App\Course', 'id', 'course_id');
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

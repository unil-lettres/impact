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

    protected $casts = [
        'deleted_at' => 'datetime',
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

    /**
     * Check if the file is used by card(s)
     *
     * @return bool
     */
    public function isUsed()
    {
        return $this->cards->isNotEmpty();
    }
}

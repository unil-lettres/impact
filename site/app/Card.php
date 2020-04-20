<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'title', 'course_id',
    ];

    /**
     * Get the course of this card.
     */
    public function course()
    {
        return $this->hasOne('App\Course', 'id', 'course_id')
            ->withTrashed();
    }

    /**
     * Get the folder of this card.
     */
    public function folder()
    {
        return $this->hasOne('App\Folder', 'id', 'folder_id');
    }

    /**
     * Get the editors of this card.
     *
     * @return Collection
     */
    public function editors()
    {
        $enrollments = $this->course->enrollments()->get()
            ->filter(function ($enrollment) {
                return $enrollment->cards ? in_array($this->id, $enrollment->cards) : false;
            });

        return $enrollments->map(function ($enrollment) {
            return $enrollment->user;
        });
    }

    /**
     * Check whether the current card is part of an active course or not
     *
     * @return boolean
     */
    public function isActive() {
        return $this->course->trashed() ? false : true;
    }
}

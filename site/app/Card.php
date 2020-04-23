<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'title', 'course_id', 'folder_id'
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

    /**
     * Get the breadcrumbs for this card
     *
     * Define if the breadcrumbs should contain the current card
     * @param bool $self
     *
     * This function will return a Collection and should contain
     * a path as the key, and a name as the value.
     * @return \Illuminate\Support\Collection
     */
    public function breadcrumbs(bool $self = false) {
        if($this->folder()->get()->isEmpty()) {
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

        if($self) {
            // Add the current card to the breadcrumbs
            $breadcrumbs->put(
                route('cards.show', $this->id), $this->title
            );
        }

        return $breadcrumbs;
    }
}

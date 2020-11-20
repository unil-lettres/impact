<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Card extends Model
{
    use SoftDeletes;

    const TRANSCRIPTION = '{
            "version": 1,
            "data": []
        }';

    const OPTIONS = '{
            "version": 1,
            "emails": true,
            "box1": {
                "hidden": false,
                "link": null,
                "start": null,
                "end": null
            },
            "box2": {
                "hidden": false,
                "sync": true
            },
            "box3": {
                "hidden": false,
                "title": "Théorie",
                "fixed": false
            },
            "box4": {
                "hidden": false,
                "title": "Exemplification",
                "fixed": false
            },
            "box5": {
                "hidden": false
            }
        }';

    protected $fillable = [
        'title', 'box2', 'box3', 'box4', 'course_id', 'folder_id', 'file_id', 'options'
    ];

    protected $dates = [
        'deleted_at'
    ];

    protected $casts = [
        'box2' => 'array',
        'options' => 'array'
    ];

    protected $attributes = [
        'box2' => Card::TRANSCRIPTION,
        'options' => Card::OPTIONS
    ];

    /**
     * Get the course of this card.
     */
    public function course()
    {
        return $this->hasOne('App\Course', 'id', 'course_id');
    }

    /**
     * Get the folder of this card.
     */
    public function folder()
    {
        return $this->hasOne('App\Folder', 'id', 'folder_id');
    }

    /**
     * Get the file of this card.
     */
    public function file()
    {
        return $this->hasOne('App\File', 'id', 'file_id');
    }

    /**
     * Get the state of this card.
     */
    public function state()
    {
        return $this->hasOne('App\State', 'id', 'state_id');
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

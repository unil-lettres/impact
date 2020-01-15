<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'title',
    ];

    /**
     * Get the course of this card.
     */
    public function course()
    {
        return $this->hasOne('App\Course', 'id', 'course_id');
    }
}

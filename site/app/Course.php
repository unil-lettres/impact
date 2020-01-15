<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Get the cards that belong to this course.
     */
    public function cards()
    {
        return $this->hasMany('App\Card', 'course_id')
            ->orderBy('created_at', 'desc');
    }
}

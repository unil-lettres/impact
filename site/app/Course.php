<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    protected $dates = [
        'deleted_at'
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

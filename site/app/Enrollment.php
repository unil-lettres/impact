<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    /**
     * Get the course of this enrollment.
     */
    public function course()
    {
        return $this->hasOne('App\Course', 'id', 'course_id');
    }

    /**
     * Get the user of this enrollment.
     */
    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
}

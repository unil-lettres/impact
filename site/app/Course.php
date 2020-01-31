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
     * Get the cards of this course.
     */
    public function cards()
    {
        return $this->hasMany('App\Card', 'course_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the enrollments of this course.
     */
    public function enrollments()
    {
        return $this->hasMany('App\Enrollment', 'course_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the role for a specific user of this course.
     *
     * @param User $user
     * @return mixed|null
     */
    public function userRole(User $user)
    {
        $enrollment = $this->enrollments()
            ->where('user_id', $user->id)
            ->first();

        return $enrollment ? $enrollment->role : null;
    }
}

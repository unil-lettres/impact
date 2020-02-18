<?php

namespace App;

use App\Enums\EnrollmentRole;
use Illuminate\Database\Eloquent\Collection;
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
     * Get all the enrollments for a specific role of this course.
     *
     * @param string $role
     *
     * @return Collection
     */
    public function enrollmentsForRole($role)
    {
        return $this->enrollments()->get()
            ->filter(function ($enrollment) use ($role) {
                return $enrollment->role === $role;
            });
    }

    /**
     * Get all the teachers of this course.
     *
     * @return Collection
     */
    public function teachers()
    {
        return $this->enrollmentsForRole(EnrollmentRole::Teacher)
            ->map(function ($enrollment) {
                return $enrollment->user;
            });
    }

    /**
     * Get all the students of this course.
     *
     * @return Collection
     */
    public function students()
    {
        return $this->enrollmentsForRole(EnrollmentRole::Student)
            ->map(function ($enrollment) {
                return $enrollment->user;
            });
    }

    /**
     * Get the role for a specific user of this course.
     *
     * @param User $user
     *
     * @return string|null
     */
    public function userRole(User $user)
    {
        $enrollment = $this->enrollments()
            ->where('user_id', $user->id)
            ->first();

        return $enrollment ? $enrollment->role : null;
    }
}

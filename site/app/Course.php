<?php

namespace App;

use App\Enums\EnrollmentRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'description', 'type', 'external_id'
    ];

    protected $dates = [
        'deleted_at'
    ];

    /**
     * Get method override for the name attribute
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->attributes['name'] ? $this->attributes['name'] : 'No name';
    }

    /**
     * Scope a query to only include local courses.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeLocal($query)
    {
        return $query->where('type', 'local');
    }

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
     * Get the invitations of this course.
     */
    public function invitations()
    {
        return $this->hasMany('App\Invitation', 'course_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the folders of this course.
     */
    public function folders()
    {
        return $this->hasMany('App\Folder', 'course_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the files of this course.
     */
    public function files()
    {
        return $this->hasMany('App\File', 'course_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all the enrollments for a specific role (EnrollmentRole) of this course.
     *
     * @param string $role
     *
     * @return Collection
     */
    public function enrollmentsForRole(string $role)
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

    /**
     * Check whether the current course is active or not
     *
     * @return boolean
     */
    public function isActive() {
        return $this->trashed() ? false : true;
    }

    /**
     * Get the root folders of this course
     *
     * @return Collection
     */
    public function rootFolders() {
        return $this->folders()->where('parent_id', null)
            ->get();
    }

    /**
     * Get the root cards of this course
     *
     * @return Collection
     */
    public function rootCards() {
        return $this->cards()->where('folder_id', null)
            ->get();
    }

    /**
     * Get the breadcrumbs for this course
     *
     * Define if the breadcrumbs should contain the current course
     * @param bool $self
     *
     * This function will return a Collection and should contain
     * a path as the key, and a name as the value.
     * @return \Illuminate\Support\Collection
     */
    public function breadcrumbs(bool $self = false) {
        $breadcrumbs = collect([
            route('home') => trans('courses.list')
        ]);

        if($self) {
            // Add the current course to the breadcrumbs
            $breadcrumbs->put(
                route('courses.show', $this->id), $this->name
            );
        }

        return $breadcrumbs;
    }
}

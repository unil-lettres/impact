<?php

namespace App;

use App\Enums\EnrollmentRole;
use DateTime;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'type', 'admin', 'creator_id', 'validity',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'validity' => 'datetime',
        'admin' => 'boolean'
    ];

    /**
     * Scope a query to only include valid users.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeValid($query)
    {
        return $query->where('validity', null)
            ->orWhere('validity', '>=', Carbon::now())
            ->orWhere('admin', true);
    }

    /**
     * Scope a query to exclude admin users.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWithoutAdmins($query)
    {
        return $query->where('admin', false);
    }

    /**
     * Get the invitations created by the user.
     */
    public function invitations()
    {
        return $this->hasMany('App\Invitation', 'creator_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the enrollments of this user.
     */
    public function enrollments()
    {
        return $this->hasMany('App\Enrollment', 'user_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the user enrollments with a teaching role.
     *
     * @return Collection
     */
    public function enrollmentsAsTeacher()
    {
        return $this->enrollments()
            ->where('role', EnrollmentRole::Teacher)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the user enrollments with a student role.
     *
     * @return Collection
     */
    public function enrollmentsAsStudent()
    {
        return $this->enrollments()
            ->where('role', EnrollmentRole::Student)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the cards with editing rights for the user.
     *
     * @return \Illuminate\Support\Collection
     */
    public function cards()
    {
        return $this->enrollmentsAsStudent()
            ->map(function ($enrollment) {
                return Card::findMany($enrollment->cards);
            })
            ->flatten();
    }

    /**
     * Check if the user is an editor of the given card.
     *
     * @param Card $card
     *
     * @return bool
     */
    public function isEditor(Card $card) {
        if($this->admin) {
            return true;
        }

        return $this->cards()
            ->contains('id', $card->id);
    }

    /**
     * Check if the user is a teacher of the given course.
     *
     * @param Course $course
     *
     * @return bool
     */
    public function isTeacher(Course $course) {
        if($this->admin) {
            return true;
        }

        return $this->enrollmentsAsTeacher()
            ->contains('course_id', $course->id);
    }

    /**
     * Check if the user is a student of the given course.
     *
     * @param Course $course
     *
     * @return bool
     */
    public function isStudent(Course $course) {
        if($this->admin) {
            return true;
        }

        return $this->enrollmentsAsStudent()
            ->contains('course_id', $course->id);
    }

    /**
     * Extend the validity of the user account.
     *
     * @param int $months
     *
     * @return DateTime
     */
    public function extendValidity(int $months = null)
    {
        $months = $months ?? config('const.users.validity');

        $validity = is_null($this->validity) ?
            Carbon::now()->addMonths($months) :
            Carbon::instance($this->validity)->addMonths($months);

        $this->update([
            'validity' => $this->skipAdmins($validity)
        ]);
        $this->save();

        return $this->validity;
    }

    /**
     * Define the validity of the user account.
     *
     * @param DateTime $validity
     *
     * @return DateTime
     */
    public function defineValidity(DateTime $validity)
    {
        $this->update([
            'validity' => $this->skipAdmins($validity)]
        );
        $this->save();

        return $this->validity;
    }

    /**
     * Avoid adding a validity to admin accounts.
     *
     * @param DateTime $validity
     *
     * @return DateTime|null
     */
    private function skipAdmins(DateTime $validity) {
        return $this->admin ? null : $validity;
    }
}

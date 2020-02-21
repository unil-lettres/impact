<?php

namespace App;

use App\Enums\EnrollmentRole;
use DateTime;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    use Notifiable;

    const DefaultValidity = 12;

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
    ];

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
        return $this->enrollmentsAsStudent()
            ->contains('course_id', $course->id);
    }

    /**
     * Extend the validity of the user account.
     * Default is 12 months.
     *
     * @param int $months
     *
     * @return DateTime
     */
    public function extendValidity(int $months = User::DefaultValidity)
    {
        $validity = is_null($this->validity) ?
            Carbon::now()->addMonths($months) :
            Carbon::instance($this->validity)->addMonths($months);

        $this->update([ 'validity' => $this->skipAdmins($validity)]);

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
        $this->update([ 'validity' => $this->skipAdmins($validity)]);

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

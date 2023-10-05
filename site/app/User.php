<?php

namespace App;

use App\Enums\EnrollmentRole;
use App\Scopes\ValidityScope;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    use Notifiable;
    use HasFactory;

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
        'admin' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new ValidityScope());
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
     * Retrieve all the users with a teacher role
     *
     * @return Collection
     */
    public static function teachers()
    {
        return Enrollment::where('role', EnrollmentRole::Teacher)->get()
            ->map(function ($enrollment) {
                return $enrollment->user;
            })
            ->unique();
    }

    /**
     * Retrieve all the users with a student role
     *
     * @return Collection
     */
    public static function students()
    {
        return Enrollment::where('role', EnrollmentRole::Student)->get()
            ->map(function ($enrollment) {
                return $enrollment->user;
            })
            ->unique();
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
     * @return bool
     */
    public function isEditor(Card $card)
    {
        if ($this->admin) {
            return true;
        }

        return $this->cards()
            ->contains('id', $card->id);
    }

    /**
     * Check if the user is a teacher of the given course.
     *
     * @return bool
     */
    public function isTeacher(Course $course)
    {
        if ($this->admin) {
            return true;
        }

        return $this->enrollmentsAsTeacher()
            ->contains('course_id', $course->id);
    }

    /**
     * Check if the user is a student of the given course.
     *
     * @return bool
     */
    public function isStudent(Course $course)
    {
        if ($this->admin) {
            return true;
        }

        return $this->enrollmentsAsStudent()
            ->contains('course_id', $course->id);
    }

    /**
     * Extend the validity of the user account.
     *
     * @return DateTime
     */
    public function extendValidity(int $months = null)
    {
        $months = $months ?? config('const.users.validity');

        // Extend validity, but do not add one to admin accounts
        $validity = $this->admin ? null : Carbon::now()->addMonths($months);

        $this->update([
            'validity' => $validity,
        ]);

        return $this->validity;
    }
}

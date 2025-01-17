<?php

namespace App;

use App\Enums\EnrollmentRole;
use App\Enums\UserType;
use App\Scopes\ValidityScope;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasFactory;
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'validity' => 'datetime',
            'admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new ValidityScope);
    }

    /**
     * Scope a query to exclude admin users.
     */
    public function scopeWithoutAdmins(Builder $query): Builder
    {
        return $query->where('admin', false);
    }

    /**
     * Scope a query to exclude aai users.
     */
    public function scopeWithoutAais(Builder $query): Builder
    {
        return $query->where('type', '!=', UserType::Aai);
    }

    /**
     * Get the invitations created by the user.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'creator_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the enrollments of this user.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the user enrollments with a teaching role.
     */
    public function enrollmentsAsManager(): Collection
    {
        return $this->enrollments()
            ->where('role', EnrollmentRole::Manager)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the user enrollments with a member role.
     */
    public function enrollmentsAsMember(): Collection
    {
        return $this->enrollments()
            ->where('role', EnrollmentRole::Member)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Retrieve all the users with a manager role.
     */
    public static function managers(): Collection
    {
        return Enrollment::where('role', EnrollmentRole::Manager)->get()
            ->map(function ($enrollment) {
                return $enrollment->user;
            })
            ->unique();
    }

    /**
     * Retrieve all the users with a member role.
     */
    public static function members(): Collection
    {
        return Enrollment::where('role', EnrollmentRole::Member)->get()
            ->map(function ($enrollment) {
                return $enrollment->user;
            })
            ->unique();
    }

    /**
     * Get the cards with editing rights for the user.
     */
    public function cards(): Collection
    {
        return $this->enrollments
            ->map(function ($enrollment) {
                return Card::findMany($enrollment->cards);
            })
            ->flatten();
    }

    /**
     * Check if the user is an holder of the given card.
     */
    public function isHolder(Card $card): bool
    {
        if ($this->admin) {
            return true;
        }

        return $this->cards()
            ->contains('id', $card->id);
    }

    /**
     * Check if the user is a manager of the given course.
     */
    public function isManager(Course $course): bool
    {
        if ($this->admin) {
            return true;
        }

        return $this->enrollmentsAsManager()
            ->contains('course_id', $course->id);
    }

    /**
     * Check if the user is a member of the given course.
     */
    public function isMember(Course $course): bool
    {
        if ($this->admin) {
            return true;
        }

        return $this->enrollmentsAsMember()
            ->contains('course_id', $course->id);
    }

    /**
     * Check the validity of the user account.
     */
    public function isValid(): bool
    {
        // Check if user is an admin
        if ($this->admin) {
            return true;
        }

        // Check if user account has an expiration date
        if (is_null($this->validity)) {
            return true;
        }

        // Check if user account is still valid
        $validity = Carbon::instance($this->validity);
        if ($validity->isFuture()) {
            return true;
        }

        return false;
    }

    /**
     * Extend the validity of the user account.
     */
    public function extendValidity(?int $months = null): ?DateTime
    {
        // Admins and AAI users have no validity
        if ($this->admin || $this->type === UserType::Aai) {
            return null;
        }

        $months = $months ?? config('const.users.validity');

        $this->update([
            'validity' => Carbon::now()
                ->addMonths($months),
        ]);

        return $this->validity;
    }

    /**
     * Check if the account is expiring in a specified number of days.
     */
    public function isAccountExpiringIn(?int $days = null): bool
    {
        // Admins and AAI users have no validity
        if ($this->admin || $this->type === UserType::Aai) {
            return false;
        }

        // Account already expired
        if (! $this->isValid()) {
            return false;
        }

        // Cannot expire if the account has no validity
        if (is_null($this->validity)) {
            return false;
        }

        $days = $days ?? config('const.users.account.expiring');

        $validity = Carbon::instance($this->validity);

        return intval(ceil(Carbon::now()->diffInDays($validity))) === $days;
    }
}

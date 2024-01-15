<?php

namespace App;

use App\Enums\EnrollmentRole;
use App\Enums\StateType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Course extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name', 'description', 'type', 'external_id', 'transcription',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Get method override for the name attribute.
     */
    public function getNameAttribute(): string
    {
        return $this->attributes['name'] ? $this->attributes['name'] : 'No name';
    }

    /**
     * Scope a query to only include local courses.
     */
    public function scopeLocal(Builder $query): Builder
    {
        return $query->where('type', 'local');
    }

    /**
     * Get the cards of this course.
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the enrollments of this course.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the invitations of this course.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the folders of this course.
     */
    public function folders(): HasMany
    {
        return $this->hasMany('App\Folder', 'course_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the files of this course.
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the states of this course.
     */
    public function states(): HasMany
    {
        return $this->hasMany(State::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the tags of this course.
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class, 'course_id')
            ->orderBy('name');
    }

    /**
     * Get all the users of this course.
     * If withTrashed is true, also return the enrollments that have been soft deleted.
     */
    public function users(bool $withTrashed = false): Collection
    {
        $enrollments = match ($withTrashed) {
            true => $this->enrollments()
                ->withTrashed()
                ->withoutGlobalScopes(),
            default => $this->enrollments(),
        };

        return $enrollments->get()
            ->map(function ($enrollment) {
                return $enrollment->user;
            });
    }

    /**
     * Get all the teachers of this course.
     */
    public function teachers(bool $withTrashed = false): Collection
    {
        return $this->enrollmentsForRole(EnrollmentRole::Teacher, $withTrashed)
            ->map(function ($enrollment) {
                return $enrollment->user;
            });
    }

    /**
     * Get all the students of this course.
     */
    public function students(bool $withTrashed = false): Collection
    {
        return $this->enrollmentsForRole(EnrollmentRole::Student, $withTrashed)
            ->map(function ($enrollment) {
                return $enrollment->user;
            });
    }

    /**
     * Get the role for a specific user of this course.
     *
     * @return string|null
     */
    public function userRole(User $user)
    {
        $enrollment = $this->enrollments()
            ->where('user_id', $user->id)
            ->first();

        return $enrollment?->role;
    }

    /**
     * Check whether the current course is active or not
     *
     * @return bool
     */
    public function isActive()
    {
        return ! $this->trashed();
    }

    /**
     * Archive all the cards of the course
     */
    public function archive(): void
    {
        // Retrieve the archived state of the course
        $state = $this->states
            ->where('type', StateType::Archived)->first();

        // Update the state of all the cards
        $this->cards()->update([
            'state_id' => $state?->id,
        ]);
    }

    /**
     * Get the root folders of this course
     */
    public function rootFolders(): Collection
    {
        return $this->folders()->where('parent_id', null)
            ->get();
    }

    /**
     * Get the root cards of this course
     */
    public function rootCards(): Collection
    {
        return $this->cards()->where('folder_id', null)
            ->get();
    }

    /**
     * Get the breadcrumbs for this course.
     *
     * The "self" parameter defines if the breadcrumbs should
     * include the current course or not.
     *
     * This function will return a Collection and should contain
     * a path as the key, and a name as the value.
     */
    public function breadcrumbs(bool $self = false): Collection
    {
        $breadcrumbs = collect([
            route('home') => trans('courses.list'),
        ]);

        if ($self) {
            // Add the current course to the breadcrumbs
            $breadcrumbs->put(
                route('courses.show', $this->id), $this->name
            );
        }

        return $breadcrumbs;
    }

    /**
     * Get all the enrollments for a specific role (EnrollmentRole) of this course.
     * If withTrashed is true, also return the enrollments that have been soft deleted.
     *
     * @param  string  $role App\Enums\EnrollmentRole
     */
    private function enrollmentsForRole(string $role, bool $withTrashed = false): Collection
    {
        $enrollments = match ($withTrashed) {
            true => $this->enrollments()
                ->withTrashed()
                ->withoutGlobalScopes(),
            default => $this->enrollments(),
        };

        return $enrollments->get()
            ->filter(function ($enrollment) use ($role) {
                return $enrollment->user && $enrollment->role === $role;
            });
    }
}

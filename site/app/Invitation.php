<?php

namespace App;

use App\Enums\InvitationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'email', 'invitation_token', 'registered_at', 'type', 'course_id', 'creator_id',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Scope a query to only active (not registered) invitations.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('registered_at');
    }

    /**
     * Scope a query to only registered invitations.
     */
    public function scopeRegistered(Builder $query): Builder
    {
        return $query->whereNotNull('registered_at');
    }

    /**
     * Get the user who created the invitation.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the course linked to the invitation.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Generate an invitation token.
     */
    public function generateInvitationToken(): string
    {
        return substr(md5(rand(0, 9).$this->email.time()), 0, 32);
    }

    /**
     * Get invitation link if available.
     */
    public function getLink(): ?string
    {
        if ($this->type === InvitationType::Aai) {
            return null;
        }

        if (! $this->invitation_token) {
            return null;
        }

        return urldecode(url('invitations/register').'?token='.$this->invitation_token);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email', 'invitation_token', 'registered_at',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

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
     *
     * @return string
     */
    public function generateInvitationToken()
    {
        return substr(md5(rand(0, 9).$this->email.time()), 0, 32);
    }

    /**
     * Get invitation link.
     *
     * @return string
     */
    public function getLink()
    {
        return urldecode(url('invitations/register').'?token='.$this->invitation_token);
    }
}

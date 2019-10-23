<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = [
        'email', 'invitation_token', 'registered_at',
    ];

    /**
     * Get the user who created the invitation.
     */
    public function creator()
    {
        return $this->hasOne('App\User', 'id', 'creator_id');
    }

    /**
     * Generate an invitation token.
     *
     * @return string
     */
    public function generateInvitationToken() {
        return substr(md5(rand(0, 9) . $this->email . time()), 0, 32);
    }

    /**
     * Get invitation link.
     *
     * @return string
     */
    public function getLink() {
        return urldecode(url('invitations/register') . '?token=' . $this->invitation_token);
    }
}

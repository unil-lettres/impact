<?php

namespace App;

use DateTime;
use Illuminate\Contracts\Auth\MustVerifyEmail;
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
        return $this->hasMany('App\Invitation', 'creator_id');
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

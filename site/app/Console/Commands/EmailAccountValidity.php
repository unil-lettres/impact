<?php

namespace App\Console\Commands;

use App\Mail\AccountValidity;
use App\User;
use Illuminate\Console\Command;
use Mail;

class EmailAccountValidity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:account:validity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email users when their account validity is about to expire';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Array of days before account expiration.
        // Each day will trigger an email to the
        // user if the account validity matches.
        $days = [config('const.users.account.expiring'), 7];

        User::withoutAdmins()
            ->withoutAais()
            ->each(function ($user) use ($days) {
                foreach ($days as $day) {
                    if ($user->isAccountExpiringIn($day)) {
                        Mail::to($user->email)
                            ->send(
                                new AccountValidity(
                                    $user,
                                    $day
                                )
                            );
                    }
                }
            });
    }
}

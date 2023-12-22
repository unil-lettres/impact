<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class ExtendValidity
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        // Extend the user's account validity automatically
        // after every login (will exclude admins & aai).
        $event->user?->extendValidity();
    }
}

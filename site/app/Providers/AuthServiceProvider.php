<?php

namespace App\Providers;

use App\Card;
use App\Course;
use App\Invitation;
use App\Policies\CardPolicy;
use App\Policies\CoursePolicy;
use App\Policies\InvitationPolicy;
use App\Policies\UserPolicy;
use App\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Invitation::class => InvitationPolicy::class,
        User::class => UserPolicy::class,
        Course::class => CoursePolicy::class,
        Card::class => CardPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}

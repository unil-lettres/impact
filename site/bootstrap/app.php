<?php

use App\Http\Middleware\CheckAai;
use App\Http\Middleware\CheckForMaintenanceMode;
use App\Http\Middleware\HasInvitation;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsValid;
use App\Http\Middleware\Localization;
use App\Http\Middleware\VerifyCsrfToken;
use App\Providers\AppServiceProvider;
use Bugsnag\BugsnagLaravel\BugsnagServiceProvider;
use Bugsnag\BugsnagLaravel\Commands\DeployCommand;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        BugsnagServiceProvider::class,
    ])
    ->withCommands([
        DeployCommand::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(AppServiceProvider::HOME);

        $middleware->append(CheckForMaintenanceMode::class);

        $middleware->web(Localization::class);

        $middleware->throttleApi('60,1');

        $middleware->group('app', [
            'is_valid' => IsValid::class,
        ]);

        $middleware->replaceInGroup('web', PreventRequestForgery::class, VerifyCsrfToken::class);

        $middleware->alias([
            'bindings' => SubstituteBindings::class,
            'check_aai' => CheckAai::class,
            'has_invitation' => HasInvitation::class,
            'is_admin' => IsAdmin::class,
        ]);

        $middleware->priority([
            StartSession::class,
            ShareErrorsFromSession::class,
            Authenticate::class,
            ThrottleRequests::class,
            AuthenticateSession::class,
            SubstituteBindings::class,
            Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

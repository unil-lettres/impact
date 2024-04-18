<?php

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        \Bugsnag\BugsnagLaravel\BugsnagServiceProvider::class,
    ])
    ->withCommands([
        \Bugsnag\BugsnagLaravel\Commands\DeployCommand::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(AppServiceProvider::HOME);

        $middleware->append(\App\Http\Middleware\CheckForMaintenanceMode::class);

        $middleware->web(\App\Http\Middleware\Localization::class);

        $middleware->throttleApi('60,1');

        $middleware->group('app', [
            'is_valid' => \App\Http\Middleware\IsValid::class,
        ]);

        $middleware->replaceInGroup('web', \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, \App\Http\Middleware\VerifyCsrfToken::class);

        $middleware->alias([
            'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'check_aai' => \App\Http\Middleware\CheckAai::class,
            'has_invitation' => \App\Http\Middleware\HasInvitation::class,
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
        ]);

        $middleware->priority([
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Auth\Middleware\Authenticate::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

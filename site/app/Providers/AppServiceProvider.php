<?php

namespace App\Providers;

use App\Card;
use App\Course;
use App\File;
use App\Folder;
use App\Observers\CardObserver;
use App\Observers\CourseObserver;
use App\Observers\FileObserver;
use App\Observers\FolderObserver;
use App\Observers\UserObserver;
use App\Policies\AttachmentPolicy;
use App\User;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->isLocal()) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the observers
        File::observe(FileObserver::class);
        Course::observe(CourseObserver::class);
        Card::observe(CardObserver::class);
        Folder::observe(FolderObserver::class);
        User::observe(UserObserver::class);
        URL::forceRootUrl(config('app.url'));

        // Use Bootstrap pagination
        Paginator::useBootstrap();

        // Define default password validation rules
        Password::defaults(function () {
            $rule = Password::min(8);

            return $this->app->isProduction()
                ? $rule->letters()
                    ->numbers()
                    ->uncompromised()
                : $rule;
        });

        // Skip trimming strings when we update a card transcription
        TrimStrings::skipWhen(function (Request $request) {
            return $request->is('cards/*/transcription');
        });

        // Map the AttachmentPolicy to itself since there is no Attachment model
        Gate::policy(AttachmentPolicy::class, AttachmentPolicy::class);

        /**
         * This is a workaround for proxies/reverse proxies that don't always pass the proper headers.
         *
         * Here, we check if the APP_URL starts with https://, which we should always honor,
         * regardless of how well the proxy or network is configured.
         */
        if ((str_starts_with(config('const.app_url'), 'https://'))) {
            URL::forceScheme('https');
        }
    }
}

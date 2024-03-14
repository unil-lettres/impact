<?php

namespace App\Providers;

use App\Card;
use App\Course;
use App\File;
use App\Folder;
use App\Http\Middleware\TrimStrings;
use App\Observers\CardObserver;
use App\Observers\CourseObserver;
use App\Observers\FileObserver;
use App\Observers\FolderObserver;
use App\Observers\UserObserver;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the observers
        File::observe(FileObserver::class);
        Course::observe(CourseObserver::class);
        Card::observe(CardObserver::class);
        Folder::observe(FolderObserver::class);
        User::observe(UserObserver::class);

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
    }
}

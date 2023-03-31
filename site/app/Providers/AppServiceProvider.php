<?php

namespace App\Providers;

use App\Card;
use App\Course;
use App\File;
use App\Observers\CardObserver;
use App\Observers\CourseObserver;
use App\Observers\FileObserver;
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
        File::observe(FileObserver::class);
        Course::observe(CourseObserver::class);
        Card::observe(CardObserver::class);
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
    }
}

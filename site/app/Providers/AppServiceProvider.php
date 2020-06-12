<?php

namespace App\Providers;

use App\Course;
use App\File;
use App\Observers\CourseObserver;
use App\Observers\FileObserver;
use Illuminate\Support\ServiceProvider;

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
    }
}

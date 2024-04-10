<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    // Override the default runningUnitTests method because the application
    // environment is not set to "testing" (default) when running tests, but
    // instead it is set to "dusk.testing".
    protected function runningUnitTests(): bool
    {
        return parent::runningUnitTests() || $this->app->environment('dusk.testing');
    }
}

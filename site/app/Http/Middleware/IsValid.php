<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if user is authenticated
        if (! Auth::user()) {
            return redirect('/');
        }

        // Check if the account is still valid
        if (auth()->user()->isValid()) {
            return $next($request);
        }

        // If the account expired, logout the user
        auth()->logout();

        // Return to the app root with error message otherwise
        return redirect()->route('login')
            ->with('error', trans('login.invalid'));
    }
}

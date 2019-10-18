<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class IsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if user is authenticated
        if(!Auth::user()) {
            return redirect('/');
        }

        // Check if user is an admin
        if(auth()->user()->admin) {
            return $next($request);
        }

        // Check if user account has an expiration date
        if(is_null(auth()->user()->validity)) {
            return $next($request);
        }

        // Check if user account is still valid
        $validity = Carbon::instance(auth()->user()->validity);
        if($validity->isFuture()) {
            return $next($request);
        }

        auth()->logout();
        // Return to the app root with error message otherwise
        return redirect()->route('login')->with('error', trans('login.invalid'));
    }
}

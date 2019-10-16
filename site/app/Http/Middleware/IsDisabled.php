<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsDisabled
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

        // Check if user account is active
        if(!auth()->user()->disabled) {
            return $next($request);
        }

        auth()->logout();
        // Return to the app root with error message otherwise
        return redirect()->route('login')->with('error', trans('login.disabled'));
    }
}

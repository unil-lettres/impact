<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;

class IsAdmin
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

        // Return 403 otherwise
        return abort(403);
    }
}

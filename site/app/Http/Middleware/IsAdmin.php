<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
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

        // Check if user is an admin
        if (auth()->user()->admin) {
            return $next($request);
        }

        // Return to the app root with error message otherwise
        return redirect('/')
            ->with('error', trans('auth.not_authorized'));
    }
}

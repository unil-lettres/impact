<?php

namespace App\Http\Middleware;

use App\Helpers\Helpers;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsValid
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if user is authenticated
        if(!Auth::user()) {
            return redirect('/');
        }

        if(Helpers::isUserValid(auth()->user())) {
            return $next($request);
        }

        auth()->logout();
        // Return to the app root with error message otherwise
        return redirect()->route('login')->with('error', trans('login.invalid'));
    }
}

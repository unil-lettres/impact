<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use App\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAai
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if user is authenticated
        if(Auth::user()) {
            return redirect('/');
        }

        // Check if the user is authenticated by SwitchAAI
        if($request->has('Shib-Identity-Provider')) {
            // Check if the user can be found in the database
            $user = User::where('email', 'aai-user@example.com')->first();

            if (!$user) {
                // If the user cannot be found, create it
                $user = $this->createAaiUser($request);
            }

            // Log the user
            Auth::login($user, true);

            return $next($request);
        }

        // Return to the app root with error message otherwise
        return redirect('/login')
            ->with('error', trans('auth.aai_failed'));
    }

    /**
     * Create a new aai user.
     *
     * @param Request $request
     * @return User $user
     */
    private function createAaiUser(Request $request): User
    {
        return User::create([
            'name' => $request->input('uid'),
            'email' => $request->input('email'),
            'type' => UserType::Aai,
        ]);
    }
}

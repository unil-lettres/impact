<?php

namespace App\Http\Middleware;

use App\Invitation;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class HasInvitation
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
        // Check for a token parameter
        if (!$request->input('token')) {
            return redirect('/login');
        }

        // Check for a matching record in the db
        try {
            $invitation = Invitation::withTrashed()
                ->where('invitation_token', $request->input('token'))
                ->firstOrFail();

            if($invitation->trashed()) {
                return redirect('/login')
                    ->with('error', trans('messages.invitation.disabled.course'));
            }
        } catch (ModelNotFoundException $e) {
            return redirect('/login')
                ->with('error', trans('messages.invitation.wrong.token'));
        }

        // Check if the invitation was already used
        if (!is_null($invitation->registered_at)) {
            return redirect('/login')
                ->with('error', trans('messages.invitation.already.used'));
        }

        return $next($request);
    }
}

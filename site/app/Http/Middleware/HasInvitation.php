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
        if (!$request->has('token')) {
            return redirect('/login');
        }

        $invitation_token = $request->get('token');

        // Check for a matching record in the db
        try {
            $invitation = Invitation::where('invitation_token', $invitation_token)->firstOrFail();
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

<?php

namespace App\Http\Middleware;

use App\Invitation;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasInvitation
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for a token parameter
        if (! $request->input('token')) {
            return redirect('/login');
        }

        // Check for a matching record in the db
        try {
            $invitation = Invitation::withTrashed()
                ->where('invitation_token', $request->input('token'))
                ->firstOrFail();

            if ($invitation->trashed()) {
                return redirect('/login')
                    ->with('error', trans('messages.invitation.disabled.course'));
            }
        } catch (ModelNotFoundException $e) {
            return redirect('/login')
                ->with('error', trans('messages.invitation.wrong.token'));
        }

        // Check if the invitation was already used
        if (! is_null($invitation->registered_at)) {
            return redirect('/login')
                ->with('error', trans('messages.invitation.already.used'));
        }

        return $next($request);
    }
}

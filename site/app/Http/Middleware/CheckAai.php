<?php

namespace App\Http\Middleware;

use App\Enrollment;
use App\Enums\EnrollmentRole;
use App\Enums\InvitationType;
use App\Enums\UserType;
use App\Invitation;
use App\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;
use Symfony\Component\HttpFoundation\Response;

class CheckAai
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (Auth::user()) {
            return redirect('/');
        }

        // Check if the user is authenticated by SwitchAAI
        if (RequestFacade::header('X-Shib-Identity-Provider')) {
            // Check if the user can be found in the database
            $user = User::where(
                'email',
                RequestFacade::header('X-Shib-Mail')
            )->first();

            if (! $user) {
                // If the user cannot be found, create it
                $user = $this->createAaiUser();
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
     */
    private function createAaiUser(): User
    {
        $user = User::create([
            'name' => RequestFacade::header('X-Shib-GivenName').' '.
                RequestFacade::header('X-Shib-Surname'),
            'email' => RequestFacade::header('X-Shib-Mail'),
            'type' => UserType::Aai,
        ]);

        $this->checkAndProcessInvitation($user);

        return $user;
    }

    /**
     * Check if an active invitation exists for the user account. If so,
     * enroll it to the course and mark the invitation as registered.
     */
    private function checkAndProcessInvitation(User $user): void
    {
        $invitation = Invitation::active()->where(
            ['email' => $user->email, 'type' => InvitationType::Aai]
        )->first();

        if ($invitation) {
            Enrollment::firstOrCreate([
                'course_id' => $invitation->course_id,
                'user_id' => $user->id,
            ], [
                'role' => EnrollmentRole::Member,
            ]);

            $invitation->update([
                'registered_at' => Carbon::now(),
            ]);
        }
    }
}

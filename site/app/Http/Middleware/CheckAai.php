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

class CheckAai
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Check if user is authenticated
        if (Auth::user()) {
            return redirect('/');
        }

        // Check if the user is authenticated by SwitchAAI
        if ($this->getServerVariable('Shib-Identity-Provider')) {
            // Check if the user can be found in the database
            $user = User::where(
                'email',
                $this->getServerVariable('mail')
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
            'name' => $this->getServerVariable('givenName').' '.
                $this->getServerVariable('surname'),
            'email' => $this->getServerVariable('mail'),
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

    /**
     * Wrapper function to be able to retrieve server variables.
     */
    private function getServerVariable(string $variableName): ?string
    {
        return RequestFacade::server($variableName) ?? RequestFacade::server('REDIRECT_'.$variableName);
    }
}

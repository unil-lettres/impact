<?php

namespace App\Http\Controllers;

use App\Course;
use App\Enrollment;
use App\Enums\CourseType;
use App\Enums\EnrollmentRole;
use App\Enums\InvitationType;
use App\Http\Requests\CreateInvitationUser;
use App\Http\Requests\ManageInvitations;
use App\Http\Requests\SendInvitationMail;
use App\Http\Requests\StoreInvitation;
use App\Invitation;
use App\Mail\InvitationCreated;
use App\Services\SwitchService;
use App\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class InvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('viewAny', Invitation::class);

        $invitations = Invitation::active()
            ->where('creator_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(config('const.pagination.per'));

        return view('invitations.index', [
            'invitations' => $invitations,
        ]);
    }

    /**
     * Display a listing of the resource in the admin panel.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function manage(ManageInvitations $request)
    {
        $this->authorize('manage', Invitation::class);

        $invitations = Invitation::active();

        // If the filter parameter is set, filter the invitations by type
        $filter = $request->get('filter');
        $invitations = $this->filter($invitations, $filter);

        // If the search parameter is set, filter the invitations by email
        $search = $request->get('search');
        $invitations = $this->search($invitations, $search);

        return view('invitations.manage', [
            'invitations' => $invitations
                ->orderBy('created_at', 'desc')
                ->paginate(config('const.pagination.per')),
            'filter' => $filter,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', [
            Invitation::class,
            null,
        ]);

        if (Auth::user()->admin) {
            $courses = Course::local()
                ->get();
        } else {
            $courses = Auth::user()->enrollmentsAsManager()
                ->filter(function ($enrollment) {
                    return $enrollment->course->type === CourseType::Local;
                })
                ->map(function ($enrollment) {
                    return $enrollment->course;
                });
        }

        return view('invitations.create', [
            'courses' => $courses,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException|Exception
     */
    public function store(StoreInvitation $request)
    {
        $course = Course::findOrFail($request->input('course'));

        $this->authorize('create', [
            Invitation::class,
            $course,
        ]);

        $type = InvitationType::Local;
        $email = $request->get('email');

        // If the Switch service is configured, check if the email is
        // registered as a SWITCHaai user through the Switch API.
        if (SwitchService::isConfigured()) {
            try {
                $type = (new SwitchService)
                    ->isEmailRegistered($email) ? InvitationType::Aai : InvitationType::Local;
            } catch (Exception $exception) {
                return redirect()->back()
                    ->with('error', $exception->getMessage());
            }
        }

        // Create new invitation
        $invitation = Invitation::create([
            'email' => $email,
            'creator_id' => Auth::user()->id,
            'course_id' => $course->id,
            'type' => $type,
        ]);

        if ($type === InvitationType::Local) {
            $invitation->update([
                'invitation_token' => $invitation->generateInvitationToken(),
            ]);
        }

        // Send invitation mail to the recipient
        Mail::to($invitation->email)->send(
            new InvitationCreated($invitation)
        );

        return redirect()->back()
            ->with('success', trans('messages.invitation.created'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function destroy(Invitation $invitation)
    {
        $this->authorize('forceDelete', $invitation);

        $invitation->forceDelete();

        return redirect()->back()
            ->with('success', trans('messages.invitation.deleted'));
    }

    /**
     * Show the form for creating a new user.
     * A valid invitation token is needed.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function register(Request $request)
    {
        $this->authorize('register', Invitation::class);

        $invitation_token = $request->get('token');
        $invitation = Invitation::where('invitation_token', $invitation_token)
            ->firstOrFail();

        return view('invitations.register', [
            'invitation' => $invitation,
        ]);
    }

    /**
     * Create a new local user instance after a valid invitation registration.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function createInvitationUser(CreateInvitationUser $request)
    {
        $this->authorize('createInvitationUser', Invitation::class);

        try {
            // Try to find an invitation for this email address
            $invitation = Invitation::where(
                ['email' => $request->input('email'), 'type' => InvitationType::Local]
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', trans('messages.invitation.user.no.match'));
        }

        // Create new user
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'creator_id' => $invitation->creator_id,
        ]);
        // Add default validity for the new user
        $user->extendValidity();

        // Create a member enrollment for the new user
        Enrollment::firstOrCreate([
            'course_id' => $invitation->course_id,
            'user_id' => $user->id,
        ], [
            'role' => EnrollmentRole::Member,
        ]);

        // Update the invitation registered_at property
        $invitation->update([
            'registered_at' => Carbon::now(),
        ]);

        // Login with created user
        Auth::login($user);

        return redirect()->route('home')
            ->with('success', trans('messages.invitation.user.created'));
    }

    /**
     * Send the invitation mail to the recipient.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function mail(SendInvitationMail $request, int $id)
    {
        $invitation = Invitation::find($id);

        $this->authorize('mail', $invitation);

        // Send invitation mail to the recipient
        Mail::to($invitation->email)->send(
            new InvitationCreated($invitation)
        );

        return redirect()->back()
            ->with('success', trans('messages.invitation.sent', ['mail' => $invitation->email]));
    }

    /**
     * Filter invitations by type
     */
    private function filter(Builder $invitations, ?string $filter): Builder
    {
        if (! $filter) {
            return $invitations;
        }

        return match ($filter) {
            InvitationType::Aai => $invitations->where('type', InvitationType::Aai),
            InvitationType::Local => $invitations->where('type', InvitationType::Local),
            default => $invitations->select('invitations.*'),
        };
    }

    /**
     * Filter invitations by email
     */
    private function search(Builder $invitations, ?string $search): Builder
    {
        if (! $search) {
            return $invitations;
        }

        return $invitations->where(function ($query) use ($search) {
            $query->where('email', 'like', '%'.$search.'%');
        });
    }
}

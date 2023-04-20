<?php

namespace App\Http\Controllers;

use App\Course;
use App\Enrollment;
use App\Enums\CourseType;
use App\Enums\EnrollmentRole;
use App\Http\Requests\CreateInvitationUser;
use App\Http\Requests\SendInvitationMail;
use App\Http\Requests\StoreInvitation;
use App\Invitation;
use App\Mail\InvitationCreated;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
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

        $invitations = Invitation::where(['registered_at' => null, 'creator_id' => Auth::user()->id])
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
    public function manage()
    {
        $this->authorize('manage', Invitation::class);

        $invitations = Invitation::where('registered_at', null)
            ->orderBy('created_at', 'desc')
            ->paginate(config('const.pagination.per'));

        return view('invitations.manage', [
            'invitations' => $invitations,
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
            $courses = Auth::user()->enrollmentsAsTeacher()
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
     * @throws AuthorizationException
     */
    public function store(StoreInvitation $request)
    {
        $course = Course::findOrFail($request->input('course'));

        $this->authorize('create', [
            Invitation::class,
            $course,
        ]);

        // Create new invitation
        $invitation = new Invitation();
        $invitation->email = $request->get('email');
        $invitation->invitation_token = $invitation->generateInvitationToken();
        $invitation->creator_id = Auth::user()->id;
        $invitation->course_id = $course->id;
        $invitation->save();

        // Send invitation mail to the recipient
        Mail::to($invitation->email)->send(new InvitationCreated($invitation));

        return redirect()->back()
            ->with('success', trans('messages.invitation.created'));
    }

    /**
     * Display the specified resource.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function show(Invitation $invitation)
    {
        $this->authorize('view', $invitation);

        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function edit(Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        return redirect()->back();
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
        $invitation = Invitation::where('invitation_token', $invitation_token)->firstOrFail();

        return view('invitations.register', [
            'invitation' => $invitation,
        ]);
    }

    /**
     * Create a new user instance after a valid invitation registration.
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
            $invitation = Invitation::where('email', $request->input('email'))->firstOrFail();
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

        // Create a student enrollment for the new user
        Enrollment::create([
            'role' => EnrollmentRole::Student,
            'course_id' => $invitation->course_id,
            'user_id' => $user->id,
        ]);

        // Update the invitation registered_at property
        $invitation->registered_at = Carbon::now();
        $invitation->update();

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
        Mail::to($invitation->email)->send(new InvitationCreated($invitation));

        return redirect()->back()
            ->with('success', trans('messages.invitation.sent', ['mail' => $invitation->email]));
    }
}

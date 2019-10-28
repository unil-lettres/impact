<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInvitationUser;
use App\Http\Requests\SendInvitationMail;
use App\Http\Requests\StoreInvitation;
use App\Invitation;
use App\Mail\InvitationCreated;
use App\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class InvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if(Auth::user()->admin) {
            // If the user is an admin, show all pending records
            $invitations = Invitation::where('registered_at', null)
                ->orderBy('created_at', 'desc')
                ->paginate(config('const.pagination.per'));
        } else {
            // If the user is not an admin, restrict the results to the records he created
            $invitations = Invitation::where(['registered_at' => null, 'creator_id' => Auth::user()->id])
                ->orderBy('created_at', 'desc')
                ->paginate(config('const.pagination.per'));
        }

        return view('invitations.index', [
            'invitations' => $invitations
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', Invitation::class);

        return view('invitations.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInvitation $request
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function store(StoreInvitation $request)
    {
        $this->authorize('create', Invitation::class);

        // Create new invitation
        $invitation = new Invitation($request->all());
        $invitation->invitation_token = $invitation->generateInvitationToken();
        $invitation->creator_id = Auth::user()->id;
        $invitation->save();

        // Send invitation mail to the recipient
        Mail::to($invitation->email)->send(new InvitationCreated($invitation));

        return redirect()->back()
            ->with('success', trans('messages.invitation.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  Invitation  $invitation
     *
     * @return Response
     */
    public function show(Invitation $invitation)
    {
        $this->authorize('view', $invitation);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Invitation  $invitation
     *
     * @return Response
     */
    public function edit(Invitation $invitation)
    {
        $this->authorize('update', $invitation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     *
     * @param  Invitation  $invitation
     * @return Response
     */
    public function update(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Invitation $invitation
     * @return Response
     * @throws Exception
     */
    public function destroy(Invitation $invitation)
    {
        $this->authorize('delete', $invitation);

        $invitation->delete();

        return redirect()->back()
            ->with('success', trans('messages.invitation.deleted'));
    }

    /**
     * Show the form for creating a new user.
     * A valid invitation token is needed.
     *
     * @param  Request $request
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function register(Request $request)
    {
        $this->authorize('register', Invitation::class);

        $invitation_token = $request->get('token');
        $invitation = Invitation::where('invitation_token', $invitation_token)->firstOrFail();

        return view('invitations.register', [
            'invitation' => $invitation
        ]);
    }

    /**
     * Create a new user instance after a valid invitation registration.
     *
     * @param CreateInvitationUser $request
     *
     * @return User
     * @throws AuthorizationException
     */
    public function createInvitationUser(CreateInvitationUser $request)
    {
        $this->authorize('createInvitationUser', Invitation::class);

        // Create new user
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        try {
            // Try to find an invitation for this email address
            $invitation = Invitation::where('email', $user->email)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', trans('messages.invitation.user.no.match'));
        }

        // Update the invitation registered_at property
        $invitation->registered_at = $user->created_at;
        $invitation->update();

        // Update the user creator_id property
        $user->creator_id = $invitation->creator_id;
        $user->update();

        // Login with created user
        Auth::login($user);

        return redirect()->route('home')
            ->with('success', trans('messages.invitation.user.created'));
    }

    /**
     * Send the invitation mail to the recipient.
     *
     * @param SendInvitationMail $request
     * @param int $id
     *
     * @return Response
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

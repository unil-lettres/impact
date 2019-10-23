<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendInvitationMail;
use App\Http\Requests\StoreInvitation;
use App\Invitation;
use App\Mail\InvitationCreated;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
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
        $invitations = Invitation::where('registered_at', null)->orderBy('created_at', 'desc')->get();
        $count = $invitations->count();

        // If the user is not an admin, restrict the results to the records he created
        if(!Auth::user()->admin) {
            $count = $invitations->where('creator_id', Auth::user()->id)->count();
        }

        return view('invitations.index', [
            'invitations' => $invitations,
            'count' => $count
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
     * @return Response
     * @throws AuthorizationException
     */
    public function register(Request $request)
    {
        $this->authorize('register', Invitation::class);

        $invitation_token = $request->get('token');
        $invitation = Invitation::where('invitation_token', $invitation_token)->firstOrFail();
        $email = $invitation->email;

        return view('invitations.register', compact('email'));
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

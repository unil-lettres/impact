<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMailingMail;
use App\Mail\ManagersMailing;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Show the admin index page.
     *
     * @return RedirectResponse
     */
    public function index()
    {
        return redirect()->route('admin.users.manage');
    }

    /**
     * Show the mailing page.
     *
     * @return Renderable
     */
    public function mailing()
    {
        $subject = '[Impact] Utilisation de vos espaces';

        $content = "Bonjour,\n\n".
            "Vous êtes actuellement responsable des espaces IMPACT suivants :\n\n".
            "{{espaces}}\n\n".
            "Nous vous remercions de nous indiquer par retour de mail si vous n'avez plus utilité d'un de ces espaces et que nous pouvons donc procéder à sa suppression. A noter que vous avez la possibilité de dupliquer une fiche d'un espace à un autre pour en conserver le contenu.\n\n".
            "Avec nos cordiales salutations,\n\n".
            Auth::user()->name."\n\n".
            'www.unil.ch/impact';

        return view('admin.mailing', [
            'subject' => $subject,
            'content' => $content,
        ]);
    }

    /**
     * Send the mailing to the managers.
     *
     * @return RedirectResponse
     */
    public function mailMailing(SendMailingMail $request)
    {
        foreach (User::managers() as $manager) {
            $courses = $manager->enrollmentsAsManager()
                ->map(function ($enrollment) {
                    return $enrollment->course;
                });

            Mail::to($manager->email)
                ->send(
                    new ManagersMailing(
                        Auth::user(),
                        $request->get('subject'),
                        $request->get('content'),
                        $courses
                    )
                );
        }

        return redirect()
            ->back()
            ->with('success', trans('messages.mailing.sent'));
    }
}

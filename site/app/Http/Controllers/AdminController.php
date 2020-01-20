<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {}

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
     * Show the users management page.
     *
     * @return Renderable
     */
    public function users()
    {
        return view('users.manage');
    }

    /**
     * Show the invitations management page.
     *
     * @return Renderable
     */
    public function invitations()
    {
        return view('invitations.manage');
    }

    /**
     * Show the courses management page.
     *
     * @return Renderable
     */
    public function courses()
    {
        return view('courses.manage');
    }
}

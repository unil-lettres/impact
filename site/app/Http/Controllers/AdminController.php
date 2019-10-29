<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;

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
     * @return Renderable
     */
    public function index()
    {
        return redirect()->route('admin.users.index');
    }

    /**
     * Show the users management page.
     *
     * @return Renderable
     */
    public function users()
    {
        return view('admin.users.index');
    }
}

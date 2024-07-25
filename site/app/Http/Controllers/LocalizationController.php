<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;

class LocalizationController extends Controller
{
    /**
     * Set the specified locale for current session.
     */
    public function index(string $locale): RedirectResponse
    {
        App::setLocale($locale);

        // Store the locale in session to get it back in the middleware
        session()->put('locale', $locale);

        return redirect()->back();
    }
}

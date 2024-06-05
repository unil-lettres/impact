<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        attemptLogin as protected authenticatesUsersAttemptLogin;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Attempt to log the user into the application.
     */
    protected function attemptLogin(Request $request)
    {
        if ($this->checkUserIsAai($request)) {
            throw ValidationException::withMessages([
                $this->username() => [trans('auth.user_is_not_local')],
            ]);
        }

        return $this->authenticatesUsersAttemptLogin($request);
    }

    /**
     * Check if the user is an AAI user.
     *
     * Return false if the user is not found.
     */
    protected function checkUserIsAai(Request $request): bool
    {
        try {
            $user = User::query()
                ->where($this->username(), $request->input($this->username()))
                ->first();
        } catch (QueryException $e) {
            $user = null;
        }

        return $user?->type === UserType::Aai;
    }
}

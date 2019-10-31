<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Http\Requests\CreateUser;
use App\Http\Requests\EditUser;
use App\Http\Requests\ExtendUser;
use App\Http\Requests\UpdateUser;
use App\Http\Requests\UpdateUserLocal;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::orderBy('created_at', 'desc')
            ->paginate(config('const.pagination.per'));

        return view('users.index', [
            'users' => $users
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
        $this->authorize('create', User::class);

        return view('auth.register');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateUser $request
     *
     * @return Response
     */
    public function store(CreateUser $request)
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $user->extendValidity();

        // TODO: translation
        return redirect()->route('admin.users.index')
            ->with('success', "User created: " . $user->email);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        // TODO: user show
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param EditUser $user
     * @param int $id
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function edit(EditUser $user, int $id)
    {
        $user = User::find($id);

        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUser $request
     * @param int $id
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function update(UpdateUser $request, int $id)
    {
        $user = User::find($id);

        $this->authorize('update', $user);

        switch ($user->type) {
            case UserType::Local:
                // Validation rules for a local user
                $validated = $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                    'old_password'     => 'nullable|string|min:8',
                    'new_password'     => 'nullable|string|min:8|different:old_password',
                    'password_confirm' => 'nullable|same:new_password',
                ]);

                // Remove empty and null values from the array data
                $validated = array_filter($validated, 'strlen');

                // If the user entered an old password, check if it's matching the db
                if(array_key_exists('old_password', $validated) &&
                    !Hash::check($validated['old_password'], $user->password)){
                    // TODO: translation
                    return back()->with('error','You have entered the wrong password');
                }

                // If the user entered a new password, replace the value by hashed version
                if(array_key_exists('new_password', $validated)) {
                    $user->password = Hash::make($validated['new_password']);
                }

                $user->email = $validated['email'];
                break;
            case UserType::Aai:
                // Validation rules for an aai user
                $validated = $request->validate([
                    'name' => 'required|string|max:255',
                ]);
                break;
            default:
                // TODO: translation
                return back()->with('error','Cannot validate user data');
                break;
        }

        $user->name = $validated['name'];

        $user->save();

        // TODO: translation
        return redirect()->route('admin.users.index')
            ->with('success', "User updated: " . $user->email);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        // TODO: translation
        return redirect()->back()
            ->with('success', 'Utilisateur supprimé');
    }

    /**
     * Extend the user account validity.
     *
     * @param ExtendUser $user
     * @param int $id
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function extend(ExtendUser $user, int $id)
    {
        $user = User::find($id);

        $this->authorize('extend', $user);

        $user->extendValidity();

        // TODO: translation
        return redirect()->route('admin.users.index')
            ->with('success', "User account validity extended: " . $user->email);
    }
}

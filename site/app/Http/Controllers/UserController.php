<?php

namespace App\Http\Controllers;

use App\Course;
use App\Enums\EnrollmentRole;
use App\Enums\UsersFilter;
use App\Enums\UserType;
use App\Http\Requests\CreateUser;
use App\Http\Requests\DestroyUser;
use App\Http\Requests\EditUser;
use App\Http\Requests\ExtendUser;
use App\Http\Requests\ManageUsers;
use App\Http\Requests\UpdateUser;
use App\Scopes\ValidityScope;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        return redirect()->back();
    }

    /**
     * Display a listing of the resource in the admin panel.
     *
     * @param ManageUsers $request
     *
     * @return Renderable
     * @throws AuthorizationException
     */
    public function manage(ManageUsers $request)
    {
        $this->authorize('manage', User::class);

        if ($request->get('filter')) {
            $users = $this->filter($request->get('filter'));
        } else {
            $users = User::withoutGlobalScope(ValidityScope::class)
                ->select('users.*');
        }

        return view('users.manage', [
            'users' => $users->orderBy('created_at', 'desc')
                ->paginate(config('const.pagination.per')),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
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
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(CreateUser $request)
    {
        $this->authorize('create', User::class);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $user->extendValidity();

        return redirect()->route('admin.users.manage')
            ->with('success', trans('messages.user.created', ['email' => $user->email]));
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param EditUser $user
     * @param int $id
     *
     * @return Renderable
     * @throws AuthorizationException
     */
    public function edit(EditUser $user, int $id)
    {
        $user = User::withoutGlobalScope(ValidityScope::class)
            ->find($id);

        $this->authorize('update', $user);

        $coursesAsTeacher = $user->enrollmentsAsTeacher()
            ->map(function ($enrollment) {
                return $enrollment->course;
            });

        $coursesAsStudent = $user->enrollmentsAsStudent()
            ->map(function ($enrollment) {
                return $enrollment->course;
            });

        return view('users.edit', [
            'user' => $user,
            'courses' => Course::local()
                ->get(),
            'teacherRole' => EnrollmentRole::Teacher,
            'coursesAsTeacher' => $coursesAsTeacher,
            'studentRole' => EnrollmentRole::Student,
            'coursesAsStudent' => $coursesAsStudent,
        ]);
    }

    /**
     * Show the profile of the specified resource.
     *
     * @param EditUser $user
     * @param int $id
     *
     * @return Renderable
     * @throws AuthorizationException
     */
    public function profile(EditUser $user, int $id)
    {
        $user = User::find($id);

        $this->authorize('update', $user);

        return view('users.profile', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUser $request
     * @param int $id
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(UpdateUser $request, int $id)
    {
        $user = User::withoutGlobalScope(ValidityScope::class)
            ->find($id);

        $this->authorize('update', $user);

        switch ($user->type) {
            case UserType::Local:
                // Validation rules for a local user
                $validated = $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
                    'old_password'     => ['nullable', 'string'],
                    'new_password'     => ['nullable', 'different:old_password', Password::defaults()],
                    'password_confirm' => ['nullable', 'same:new_password'],
                ]);

                // Remove empty and null values from the array data
                $validated = array_filter($validated, 'strlen');

                // If the user entered an old password, check if it's matching the db
                if (array_key_exists('old_password', $validated) &&
                    ! Hash::check($validated['old_password'], $user->password)) {
                    return back()->with('error', trans('messages.user.edit.wrong.password'));
                }

                // If the user entered a new password, replace the value by hashed version
                if (array_key_exists('new_password', $validated)) {
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
                return redirect()->back()
                    ->with('error', trans('messages.user.edit.cannot.validate'));
                break;
        }

        $user->name = $validated['name'];

        // Allow change of the user admin parameter only if the current user is already an admin
        if (auth()->user()->admin) {
            $user->admin = (bool) $request->input('admin');
        }

        $user->save();

        return redirect()->back()
            ->with('success', trans('messages.user.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyUser $request
     * @param int $id
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy(DestroyUser $request, int $id)
    {
        $user = User::withoutGlobalScope(ValidityScope::class)
            ->find($id);

        $this->authorize('delete', $user);

        $email = $user->email;
        $user->delete();

        return redirect()->back()
            ->with('success', trans('messages.user.deleted', ['email' => $email]));
    }

    /**
     * Extend the user account validity.
     *
     * @param ExtendUser $request
     * @param int $id
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function extend(ExtendUser $request, int $id)
    {
        $user = User::withoutGlobalScope(ValidityScope::class)
            ->find($id);

        $this->authorize('extend', $user);

        $user->extendValidity();

        return redirect()->route('admin.users.manage')
            ->with('success', trans('messages.user.validity.extended', ['email' => $user->email]));
    }

    /**
     * Filter users by parameter
     *
     * @param string $filter
     *
     * @return Builder
     */
    private function filter(string $filter)
    {
        $filters = User::query();

        $filters->withoutGlobalScope(ValidityScope::class);

        switch ($filter) {
            case UsersFilter::Expired:
                $filters
                    ->whereDate('validity', '<=', Carbon::now());
                break;
            case UsersFilter::Aai:
                $filters
                    ->where('type', UserType::Aai);
                break;
            case UsersFilter::Local:
                $filters
                    ->where('type', UserType::Local);
                break;
            default:
                $filters
                    ->select('users.*');
        }

        return $filters;
    }
}

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
use App\Mail\LocalUserCreated;
use App\Scopes\ValidityScope;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource in the course configuration.
     *
     * @throws AuthorizationException
     */
    public function index(Course $course): Renderable
    {
        $this->authorize('viewAny', [User::class, $course]);

        return view('users.registration', [
            'course' => $course,
            'breadcrumbs' => $course
                ->breadcrumbs(true),
            'users' => User::all(),
            'managerRole' => EnrollmentRole::Manager,
            'usersAsManager' => $course->managers(),
            'memberRole' => EnrollmentRole::Member,
            'usersAsMember' => $course->members(),
        ]);
    }

    /**
     * Display a listing of the resource in the admin panel.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function manage(ManageUsers $request)
    {
        $this->authorize('manage', User::class);

        $users = User::query()
            ->withoutGlobalScope(ValidityScope::class)
            ->select('users.*');

        // If the filter parameter is set, filter the users by type
        $filter = $request->get('filter');
        $users = $this->filter($users, $filter);

        // If the search parameter is set, filter the users by name and email
        $search = $request->get('search');
        $users = $this->search($users, $search);

        return view('users.manage', [
            'users' => $users->orderBy('created_at', 'desc')
                ->paginate(config('const.pagination.per')),
            'filter' => $filter,
            'search' => $search,
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
        $this->authorize('create', User::class);

        return view('auth.register');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return RedirectResponse
     *
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

        // Send local user created mail to the recipient
        Mail::to($user->email)->send(
            new LocalUserCreated($user, $request->input('password'))
        );

        return redirect()->route('admin.users.manage')
            ->with('success', trans('messages.user.created', ['email' => $user->email]));
    }

    /**
     * Display the specified resource.
     *
     * @return RedirectResponse
     *
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
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function edit(EditUser $user, int $id)
    {
        $user = User::withoutGlobalScope(ValidityScope::class)
            ->find($id);

        $this->authorize('update', $user);

        $coursesAsManager = $user->enrollmentsAsManager()
            ->map(function ($enrollment) {
                return $enrollment->course;
            });

        $coursesAsMember = $user->enrollmentsAsMember()
            ->map(function ($enrollment) {
                return $enrollment->course;
            });

        return view('users.edit', [
            'user' => $user,
            'courses' => Course::local()
                ->get(),
            'managerRole' => EnrollmentRole::Manager,
            'coursesAsManager' => $coursesAsManager,
            'memberRole' => EnrollmentRole::Member,
            'coursesAsMember' => $coursesAsMember,
        ]);
    }

    /**
     * Show the profile of the specified resource.
     *
     * @return Renderable
     *
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
     * @return RedirectResponse
     *
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
                    'name' => 'sometimes|string|max:255',
                    'email' => 'sometimes|string|email|max:255|unique:users,email,'.$user->id,
                    'old_password' => ['nullable', 'string'],
                    'new_password' => ['nullable', 'different:old_password', Password::defaults()],
                    'password_confirm' => ['nullable', 'same:new_password'],
                ]);

                // Remove null values from the array data
                $validated = Arr::whereNotNull($validated);

                // If the user entered an old password, check if it's matching the db
                if (array_key_exists('old_password', $validated) &&
                    ! Hash::check($validated['old_password'], $user->password)) {
                    return back()->with('error', trans('messages.user.edit.wrong.password'));
                }

                // If the user entered a new password, replace the old value with the new one
                if (array_key_exists('new_password', $validated)) {
                    $user->password = $validated['new_password'];
                }
                break;
            case UserType::Aai:
                // Validation rules for an aai user
                $validated = $request->validate([
                    'name' => 'sometimes|string|max:255',
                    'email' => 'sometimes|string|email|max:255|unique:users,email,'.$user->id,
                ]);
                break;
            default:
                return redirect()->back()
                    ->with('error', trans('messages.user.edit.cannot.validate'));
        }

        // Allow change of the following parameters only if the request is
        // coming from the users administration page. They are not allowed
        // to be changed from the user profile page.
        if ($request->route()->named('admin.users.update')) {
            $user->name = $validated['name'];
            $user->email = $validated['email'];

            // Allow change of the admin parameter only if the current
            // user is already an admin
            if (auth()->user()->admin) {
                $user->admin = (bool) $request->input('admin');

                // If the user becomes an admin, ensure the validity is null
                // (admin users have no validity)
                if ($user->admin) {
                    $user->validity = null;
                }
            }
        }

        // Save the user to the database
        $user->save();

        $routeName = $request->route()->named('admin.users.update') ?
            'admin.users.manage' : 'home';

        return redirect()->route($routeName)
            ->with('success', trans('messages.user.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function destroy(DestroyUser $request, int $id)
    {
        $user = User::withoutGlobalScope(ValidityScope::class)
            ->find($id);

        $this->authorize('delete', $user);

        $email = $user->email;
        // Delete the record from the database. Any invitation
        // related to the user email address will be deleted
        // with the UserObserver "deleted" event.
        $user->delete();

        return redirect()->back()
            ->with('success', trans('messages.user.deleted', ['email' => $email]));
    }

    /**
     * Extend the user account validity.
     *
     * @return RedirectResponse
     *
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
     * Filter users by type
     */
    private function filter(Builder $users, ?string $filter): Builder
    {
        if (! $filter) {
            return $users;
        }

        return match ($filter) {
            UsersFilter::Expired => $users->whereDate('validity', '<=', Carbon::now()),
            UsersFilter::Aai => $users->where('type', UserType::Aai),
            UsersFilter::Local => $users->where('type', UserType::Local),
            default => $users->select('users.*'),
        };
    }

    /**
     * Filter users by name and email
     */
    private function search(Builder $users, ?string $search): Builder
    {
        if (! $search) {
            return $users;
        }

        return $users->where(function ($query) use ($search) {
            $query->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%');
        });
    }
}

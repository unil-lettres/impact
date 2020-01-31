@extends(Route::is('admin.users.edit') ? 'layouts.app-admin' : 'layouts.app-base')

@section(Route::is('admin.users.edit') ? 'admin.content' : 'content')
    <div id="edit-user">
        <div class="row">
            <div class="col-md-12 col-lg-8">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div><br />
                @endif
                <div class="card">
                    <div class="card-header">
                        <span class="title">{{ $user->name ? $user->name : $user->email }}</span>
                        @unless (Helpers::isUserValid($user))
                            <span class="badge badge-danger">{{ trans('users.expired') }}</span>
                        @endunless

                        @can('extend', $user)
                            <a href="{{ route('admin.users.extend', $user->id) }}"
                               data-toggle="tooltip"
                               data-placement="top"
                               class="btn btn-primary float-right extend-validity"
                               title="{{ trans('users.validity.extend', ['months' => App\User::DefaultValidity]) }}">
                                <i class="far fa-clock"></i>
                            </a>
                        @endcan
                    </div>
                    <div class="card-body">
                        <form method="post"
                              action="{{ Route::is('admin.users.edit') ? route('admin.users.update', $user->id) : route('users.profile.update', $user->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label">{{ trans('users.name') }}</label>
                                <div class="col-md-8">
                                    <input id="name"
                                           type="text"
                                           name="name"
                                           value="{{ old('name', $user->name) }}"
                                           class="form-control"
                                    >
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label">{{ trans('users.email') }}</label>
                                <div class="col-md-8">
                                    <input id="email"
                                           type="email"
                                           name="email"
                                           value="{{ old('email', $user->email) }}"
                                           class="form-control {{ Helpers::isUserLocal($user) ? '' : 'disabled' }}"
                                           {{ Helpers::isUserLocal($user) ? '' : 'disabled' }}
                                    >
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="type" class="col-md-4 col-form-label">{{ trans('users.type') }}</label>
                                <div class="col-md-8">
                                    <input id="type"
                                           type="text"
                                           name="type"
                                           value="{{ old('type', $user->type) }}"
                                           class="form-control disabled"
                                           disabled
                                    >
                                </div>
                            </div>

                            @if (Helpers::isUserLocal($user))
                                @if ($user->validity)
                                    <div class="form-group row">
                                        <label for="validity" class="col-md-4 col-form-label">{{ trans('users.validity') }}</label>
                                        <div class="col-md-8">
                                            <input id="type"
                                                   type="text"
                                                   name="validity"
                                                   value="{{ $user->validity->format('d/m/Y H:i:s') }}"
                                                   class="form-control disabled {{ Helpers::isUserValid($user) ? '' : 'account-expired' }}"
                                                   disabled
                                            >
                                        </div>
                                    </div>
                                @endif

                                <div class="form-group row">
                                    <label for="old_password" class="col-md-4 col-form-label">{{ trans('users.password.current') }}</label>
                                    <div class="col-md-8">
                                        <input id="old_password"
                                               name="old_password"
                                               type="password"
                                               class="form-control"
                                        >
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="new_password" class="col-md-4 col-form-label">{{ trans('users.password.new') }}</label>
                                    <div class="col-md-8">
                                        <input id="new_password"
                                               name="new_password"
                                               type="password"
                                               class="form-control"
                                        >
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="password_confirm" class="col-md-4 col-form-label">{{ trans('users.password.confirm') }}</label>
                                    <div class="col-md-8">
                                        <input id="password_confirm"
                                               name="password_confirm"
                                               type="password"
                                               class="form-control"
                                        >
                                    </div>
                                </div>
                            @endif

                            <button type="submit" class="btn btn-primary">
                                {{ trans('users.update') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <span class="title">{{ trans('enrollments.enrollments') }}</span>
                        </div>
                        <div class="card-body">
                            @if ($user->enrollments()->count() > 0)
                                @if ($user->enrollmentsAsTeacher()->isNotEmpty())
                                    {{ trans('enrollments.as_teacher') }}
                                    <ul>
                                        @foreach ($user->enrollmentsAsTeacher() as $enrollment)
                                            <li><a href="{{ route('courses.show', $enrollment->course->id) }}">{{ $enrollment->course->name }}</a></li>
                                        @endforeach
                                    </ul>
                                @endif
                                @if ($user->enrollmentsAsStudent()->isNotEmpty())
                                    {{ trans('enrollments.as_student') }}
                                    <ul>
                                        @foreach ($user->enrollmentsAsStudent() as $enrollment)
                                            <li><a href="{{ route('courses.show', $enrollment->course->id) }}">{{ $enrollment->course->name }}</a></li>
                                        @endforeach
                                    </ul>
                                @endif
                            @else
                                <p class="text-secondary">
                                    {{ trans('enrollments.not_found') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <span class="title">{{ trans('cards.my_cards') }}</span>
                        </div>
                        <div class="card-body">
                            <!-- TODO: list all the cards owned by the user  -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

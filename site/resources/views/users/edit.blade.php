@extends('layouts.app-admin')

@section('admin.content')
    <div id="edit-user">
        <div class="row">
            <div class="col-md-12 col-lg-7">
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
                        @unless ($user->isValid())
                            <span class="badge bg-danger">{{ trans('users.expired') }}</span>
                        @endunless

                        @can('extend', $user)
                            <a href="{{ route('admin.users.extend', $user->id) }}"
                               data-bs-toggle="tooltip"
                               data-placement="top"
                               class="btn btn-primary float-end extend-validity"
                               title="{{ trans('users.validity.extend', ['months' => config('const.users.validity')]) }}">
                                <i class="far fa-clock"></i>
                            </a>
                        @endcan
                    </div>
                    <div class="card-body">
                        <form method="post"
                              action="{{ route('admin.users.update', $user->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="col-12 mb-3 row">
                                <label for="name" class="col-md-4 col-form-label">
                                    {{ trans('users.name') }}
                                </label>
                                <div class="col-md-8">
                                    <input id="name"
                                           type="text"
                                           name="name"
                                           value="{{ old('name', $user->name) }}"
                                           class="form-control"
                                    >
                                </div>
                            </div>

                            <div class="col-12 mb-3 row">
                                <label for="email" class="col-md-4 col-form-label">
                                    {{ trans('users.email') }}
                                </label>
                                <div class="col-md-8">
                                    <input id="email"
                                           type="email"
                                           name="email"
                                           value="{{ old('email', $user->email) }}"
                                           class="form-control"
                                    >
                                    @if(!Helpers::isUserLocal($user))
                                        <div class="form-text text-danger">
                                            {{ trans('users.email.warning') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-12 mb-3 row">
                                <label for="type" class="col-md-4 col-form-label">
                                    {{ trans('users.type') }}
                                </label>
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
                                    <div class="col-12 mb-3 row">
                                        <label for="validity" class="col-md-4 col-form-label">
                                            {{ trans('users.validity') }}
                                        </label>
                                        <div class="col-md-8">
                                            <input id="validity"
                                                   type="text"
                                                   name="validity"
                                                   value="{{ $user->validity->format('d/m/Y H:i:s') }}"
                                                   class="form-control disabled {{ $user->isValid() ? '' : 'account-expired' }}"
                                                   disabled
                                            >
                                        </div>
                                    </div>
                                @endif

                                <div class="col-12 mb-3 row">
                                    <label for="old_password" class="col-md-4 col-form-label">
                                        {{ trans('users.password.current') }}
                                    </label>
                                    <div class="col-md-8">
                                        <input id="old_password"
                                               name="old_password"
                                               type="password"
                                               class="form-control"
                                        >
                                    </div>
                                </div>
                                <div class="col-12 mb-3 row">
                                    <label for="new_password" class="col-md-4 col-form-label">
                                        {{ trans('users.password.new') }}
                                    </label>
                                    <div class="col-md-8">
                                        <input id="new_password"
                                               name="new_password"
                                               type="password"
                                               class="form-control"
                                        >
                                    </div>
                                </div>
                                <div class="col-12 mb-3 row">
                                    <label for="password_confirm" class="col-md-4 col-form-label">
                                        {{ trans('users.password.confirm') }}
                                    </label>
                                    <div class="col-md-8">
                                        <input id="password_confirm"
                                               name="password_confirm"
                                               type="password"
                                               class="form-control"
                                        >
                                    </div>
                                </div>
                            @endif
                            <div class="col-12 mb-3 row">
                                <label for="admin" class="col-md-4 form-label">
                                    {{ trans('users.admin') }}
                                </label>
                                <div class="col-md-8">
                                    <div class="form-check">
                                        <input id="admin"
                                               type="checkbox"
                                               name="admin"
                                               {{ old('admin', $user->admin) ? 'checked' : '' }}
                                               class="form-check-input"
                                        >
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <button type="submit"
                                    class="btn btn-primary"
                                    dusk="user-update-button">
                                {{ trans('users.update') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <span class="title">{{ trans('enrollments.enrollments') }}</span>
                    </div>
                    <div class="card-body">
                        {{ trans('enrollments.as_manager') }}
                        <div id="rct-multi-course-manager-select"
                             class="mb-3"
                             data='{{ json_encode(['record' => $user, 'role' => $managerRole, 'options' => $courses, 'defaults' => $coursesAsManager, 'isDisabled' => !$user->isValid()]) }}'
                        ></div>
                        {{ trans('enrollments.as_member') }}
                        <div id="rct-multi-course-member-select"
                             data='{{ json_encode(['record' => $user, 'role' => $memberRole, 'options' => $courses, 'defaults' => $coursesAsMember, 'isDisabled' => !$user->isValid()]) }}'
                        ></div>
                        <hr>
                        <div class="form-text">{{ trans('users.edit.enrollments_are_auto_save') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

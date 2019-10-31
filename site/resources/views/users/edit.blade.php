@extends('layouts.app-admin')

@section('admin.menu')
    @include('admin.menu')
@stop
<!-- TODO: translations -->
@section('admin.content')
    <div id="edit-user">
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
                    <span class="account-expired">(user account expired)</span>
                @endif
                @can('extend', $user)
                    <a href="{{ route('admin.users.extend', $user->id) }}"
                       data-toggle="tooltip"
                       data-placement="top"
                       class="btn btn-primary float-right"
                       title="Extend validity ({{ App\User::DefaultValidity }} months)">
                        <i class="far fa-clock"></i>
                    </a>
                @endcan
            </div>
            <div class="card-body">
                <form method="post"
                      action="{{ route('admin.users.update', $user->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group row">
                        <label for="name" class="col-md-2 col-form-label">Name</label>
                        <div class="col-md-6">
                            <input id="name"
                                   type="text"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   class="form-control"
                            >
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="email" class="col-md-2 col-form-label">Email</label>
                        <div class="col-md-6">
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
                        <label for="type" class="col-md-2 col-form-label">Type</label>
                        <div class="col-md-6">
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
                        @if (!$user->admin && $user->validity)
                            <div class="form-group row">
                                <label for="validity" class="col-md-2 col-form-label">Validity</label>
                                <div class="col-md-6">
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
                            <label for="old_password" class="col-md-2 col-form-label">{{ __('Current password') }}</label>
                            <div class="col-md-6">
                                <input id="old_password"
                                       name="old_password"
                                       type="password"
                                       class="form-control"
                                >
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="new_password" class="col-md-2 col-form-label">{{ __('New password') }}</label>
                            <div class="col-md-6">
                                <input id="new_password"
                                       name="new_password"
                                       type="password"
                                       class="form-control"
                                >
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="password_confirm" class="col-md-2 col-form-label">{{ __('Confirm password') }}</label>
                            <div class="col-md-6">
                                <input id="password_confirm"
                                       name="password_confirm"
                                       type="password"
                                       class="form-control"
                                >
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-primary">
                        Update account
                    </button>
                </form>
            </div>
        </div>
    </div>
@stop

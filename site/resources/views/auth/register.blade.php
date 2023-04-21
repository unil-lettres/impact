@extends('layouts.app-admin')

@section('admin.content')
    <div class="container">
        <div class="row justify-content-start">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">{{ trans('login.register') }}</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.users.store') }}">
                            @csrf

                            <div class="col-12 mb-3 row">
                                <label for="name" class="col-md-2 col-form-label">{{ trans('login.name') }}</label>
                                <div class="col-md-6">
                                    <input id="name"
                                           type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           name="name" value="{{ old('name') }}"
                                           required
                                           autocomplete="name"
                                           autofocus
                                    >
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12 mb-3 row">
                                <label for="email" class="col-md-2 col-form-label">{{ trans('login.email') }}</label>
                                <div class="col-md-6">
                                    <input id="email"
                                           type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           name="email" value="{{ old('email') }}"
                                           required
                                           autocomplete="email"
                                    >
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12 mb-3 row">
                                <label for="password" class="col-md-2 col-form-label">{{ trans('login.password') }}</label>
                                <div class="col-md-6">
                                    <input id="password"
                                           type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           name="password"
                                           required
                                           autocomplete="new-password"
                                    >
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12 mb-3 row">
                                <label for="password-confirm" class="col-md-2 col-form-label">{{ trans('login.password_confirm') }}</label>
                                <div class="col-md-6">
                                    <input id="password-confirm"
                                           type="password"
                                           class="form-control"
                                           name="password_confirmation"
                                           required
                                           autocomplete="new-password"
                                    >
                                </div>
                            </div>

                            <div class="col-12 row mb-0">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary">
                                        {{ trans('login.register') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

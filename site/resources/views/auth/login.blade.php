@extends('layouts.app-base')

@section('content')
<div class="container">
    <div class="row justify-content-center login-forms">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">{{ trans('login.aai') }}</div>

                <div class="card-body">
                    <div>
                        <a class="legacy" target="_blank" href="https://www.switch.ch/aai/about/">{{ trans('login.aai.about') }}</a> |
                        <a class="legacy" target="_blank" href="https://www.switch.ch/aai/faq/">{{ trans('login.aai.faq') }}</a> |
                        <a class="legacy" target="_blank" href="https://www.switch.ch/aai/help/">{{ trans('login.aai.help') }}</a> |
                        <a class="legacy" target="_blank" href="https://www.switch.ch/aai/privacy/">{{ trans('login.aai.privacy') }}</a>
                    </div>
                    <hr>
                    <p class="text-secondary">{!! trans('login.aai_info') !!}</p>
                    <div class="col text-center">
                        <a href="{{ route('aai') }}"
                           class="btn btn-primary btn-lg">
                            SWITCHaai
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">{{ trans('login.local') }}</div>

                <div class="card-body">
                    <p class="text-secondary">{{ trans('login.local_info') }}</p>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="col-12 mb-3 row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ trans('login.email') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 mb-3 row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ trans('login.password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 mb-3 row">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label form-label" for="remember">
                                        {{ trans('login.remember_me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ trans('login.login') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link legacy" href="{{ route('password.request') }}">
                                        {{ trans('login.forgot_password') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app-base')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ trans('login.verify_email') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ trans('login.verification_link_sent') }}
                        </div>
                    @endif

                    {{ trans('login.verification_link_check_email') }}
                    {{ trans('login.email_missing') }},
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ trans('login.new_verification_link') }}</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

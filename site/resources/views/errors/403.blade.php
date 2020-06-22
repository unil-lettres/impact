@extends('layouts.app-base')

@section('content')
    <div class="text-center">
        <h1>403</h1>
        <h5>{{ trans('errors.not_authorized') }}</h5>
    </div>
@endsection

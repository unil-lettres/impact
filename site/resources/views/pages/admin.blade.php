@extends('layouts.app-base')

@section('title')
    {{ trans('login.admin') }}
@stop

@section('content')
    <div>
        Administration content ({{ Helpers::currentLocal() }})
    </div>
@stop

@extends('layouts.app-base')

@section('breadcrumbs')
    <a href="/admin">Admin</a>
@stop

@section('content')
    <div>
        Administration content ({{ Helpers::currentLocal() }})
    </div>
@stop

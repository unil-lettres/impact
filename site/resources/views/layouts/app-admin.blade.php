@extends('layouts.app-base')

@section('admin.menu')
    @include('admin.menu')
@stop

@section('content')
    <div class="admin-content">
        @yield('admin.content')
    </div>
@stop

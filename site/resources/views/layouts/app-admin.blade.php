@extends('layouts.app-base')

@section('menu')
    @include('admin.menu')
@stop

@section('content')
    <div class="admin-content">
        @yield('admin.content')
    </div>
@stop

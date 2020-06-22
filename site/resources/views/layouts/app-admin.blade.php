@extends('layouts.app-base')

@section('menu')
    @include('admin.menu')
@endsection

@section('content')
    <div class="admin-content">
        @yield('admin.content')
    </div>
@endsection

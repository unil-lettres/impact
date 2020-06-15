@extends('layouts.app-admin')

@section('admin.content')
    @can('manage', \App\File::class)
        @include('files.list')
    @endcan
@endsection

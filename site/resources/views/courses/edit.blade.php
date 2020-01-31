@extends('layouts.app-admin')

@section('admin.content')
    <div id="edit-course">
        @can('update', $course)
            Edit the content of "{{ $course->name }}"
        @endcan
    </div>
@stop

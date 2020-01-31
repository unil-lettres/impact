@extends('layouts.app-base')

@section('content')
    <div id="configure-course">
        @can('configure', $course)
            Configure the parameters of "{{ $course->name }}"
        @endcan
    </div>
@endsection

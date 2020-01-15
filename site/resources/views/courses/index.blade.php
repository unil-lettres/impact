@extends('layouts.app-base')

@section('content')
    <div id="courses">
        List of courses ({{ $courses->total() }})
    </div>
@endsection

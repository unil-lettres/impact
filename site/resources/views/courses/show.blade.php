@extends('layouts.app-base')

@section('content')
    <div id="course">
        <div>Content of "{{ $course->name }}"</div>
        <div>Number of cards in this course: {{ $cards->count() }}</div>
    </div>
@endsection

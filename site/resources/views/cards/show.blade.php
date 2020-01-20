@extends('layouts.app-base')

@section('content')
    <div id="card">
        @section('title')
            {{ $card->title }}
        @endsection
        <hr>
        <div>This card belongs to the "{{ $course->name }}"</div>
    </div>
@endsection

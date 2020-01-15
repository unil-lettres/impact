@extends('layouts.app-base')

@section('content')
    <div id="card">
        <div>Content of "{{ $card->title }}"</div>
        <div>This card belongs to the "{{ $course->name }}"</div>
    </div>
@endsection

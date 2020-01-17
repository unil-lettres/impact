@extends('layouts.app-base')

@section('content')
    <div id="courses">
        List of spaces ({{ $courses->count() }})
    </div>
@endsection

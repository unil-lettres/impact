@extends('layouts.app-base')

@section('content')
    <div id="cards">
        List of cards ({{ $cards->total() }})
    </div>
@endsection

@extends('layouts.app-base')

<!-- TODO: translate -->

@section('content')
    <div id="card">
        @section('title')
            {{ $card->title }}

            <a href="{{ route('cards.edit', $card->id) }}"
               class="btn btn-primary float-right">
                Configurer la fiche
            </a>
        @endsection
        <hr>
        <div>
            Content of the card<br>
            (This card belongs to the "{{ $course->name }}")
        </div>
    </div>
@endsection

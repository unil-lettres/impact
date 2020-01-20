@extends('layouts.app-base')

@section('content')
    <div id="course">
        @section('title')
            {{ $course->name }}

            <a href="{{ route('cards.create', ['course' => $course->id]) }}"
               class="btn btn-primary float-right">
                {{ trans('cards.create') }}
            </a>
        @endsection
        <hr>
        <div>
            @unless ($cards->isEmpty())
                <ul>
                    @foreach ($cards as $card)
                        <li><a href="{{ route('cards.show', $card->id) }}">{{ $card->title }}</a></li>
                    @endforeach
                </ul>
            @else
                <p class="text-secondary">
                    {{ trans('cards.not_found') }}
                </p>
            @endunless
        </div>
    </div>
@endsection

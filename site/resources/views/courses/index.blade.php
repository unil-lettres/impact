@extends('layouts.app-base')

@section('content')
    <div id="courses">
        @section('title')
            {{ trans('courses.list') }}
        @endsection
        <hr>
        <div>
            @unless ($courses->isEmpty())
                <ul>
                    @foreach ($courses as $course)
                        <li><a href="{{ route('courses.show', $course->id) }}">{{ $course->name }}</a></li>
                    @endforeach
                </ul>
            @else
                <p class="text-secondary">
                    {{ trans('courses.not_found') }}
                </p>
            @endunless
        </div>
    </div>
@endsection

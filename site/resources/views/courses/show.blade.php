@extends('layouts.app-base')

@section('content')
    <div id="course">
        @can('view', $course)
            @section('title')
                {{ $course->name }}

                <div class="dropdown show float-right">
                    <a class="btn btn-primary dropdown-toggle" href="#" role="button" id="dropdownCourseMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Actions
                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownCourseMenuLink">
                        <a class="dropdown-item" href="{{ route('cards.create', ['course' => $course->id]) }}">
                            {{ trans('cards.create') }}
                        </a>
                        @can('configure', $course)
                            <a class="dropdown-item" href="{{ route('courses.configure', $course->id) }}">
                                {{ trans('courses.configure') }}
                            </a>
                        @endcan
                    </div>
                </div>
            @endsection
            <hr>
            <div>
                @unless ($cards->isEmpty())
                    <ul>
                        @foreach ($cards as $card)
                            <li>
                                <a href="{{ route('cards.show', $card->id) }}">{{ $card->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-secondary">
                        {{ trans('cards.not_found') }}
                    </p>
                @endunless
            </div>
        @endcan
    </div>
@endsection

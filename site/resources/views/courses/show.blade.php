@extends('layouts.app-base')

@section('content')
    <div id="course">
        @can('view', $course)
            @section('title')
                {{ $course->name }}

                @if (Auth::user()->isTeacher($course))
                    <div class="dropdown show float-right">
                        <a class="btn btn-primary dropdown-toggle"
                           href="#"
                           role="button"
                           id="dropdownCourseMenuLink"
                           data-toggle="dropdown"
                           aria-haspopup="true"
                           aria-expanded="false">
                            Actions
                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownCourseMenuLink">
                            @can('create', [\App\Card::class, $course])
                                <a class="dropdown-item" href="{{ route('cards.create', ['course' => $course->id]) }}">
                                    {{ trans('cards.create') }}
                                </a>
                            @endcan

                            @can('configure', $course)
                                <a class="dropdown-item" href="{{ route('courses.configure', $course->id) }}">
                                    {{ trans('courses.configure') }}
                                </a>
                            @endcan
                        </div>
                    </div>
                @endunless
            @endsection
            <hr>
            <div>
                @unless ($cards->isEmpty())
                    <ul>
                        @foreach ($cards as $card)
                            @can('view', $card)
                                <li>
                                    <a href="{{ route('cards.show', $card->id) }}">{{ $card->title }}</a>
                                    @can('delete', $card)
                                        <form class="with-delete-confirm" method="post" style="display: inline;"
                                              action="{{ route('cards.destroy', $card->id) }}">
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-link"
                                                    style="color: red; padding: 0;">
                                                ({{ trans('cards.delete') }})
                                            </button>
                                        </form>
                                    @endcan
                                </li>
                            @endcan
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

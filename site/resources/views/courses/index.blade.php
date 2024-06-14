@extends('layouts.app-base')

@section('title')
    {{ trans('courses.list') }}
@endsection

@section('actions')
    @if(Auth::user()->admin)
        <div class="dropdown show">
            <a class="btn dropdown-toggle{{ $filter ? ' btn-primary' : ' btn-secondary'  }}"
               href="#"
               role="button"
               id="dropdownCoursesFiltersLink"
               data-bs-toggle="dropdown"
               aria-haspopup="true"
               aria-expanded="false">
                {{ trans('admin.filters') }}
                <i class="fa-solid{{ $filter ? ' fa-check' : '' }}"></i>
            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownCoursesFiltersLink">
                <a class="dropdown-item" href="{{ route('home') }}">
                    -
                </a>
                <a class="dropdown-item"
                   href="{{ route('home', ['filter' => \App\Enums\CoursesFilter::Own]) }}">
                    {!! Helpers::filterSelectedMark($filter, \App\Enums\CoursesFilter::Own) !!}
                    {{ trans('courses.own') }}
                </a>
            </div>
        </div>
    @endif
@endsection

@section('content')
    <div id="index-courses">
        @unless ($courses->isEmpty())
            <div class="flex-table">
                <div class="flex-row header">
                    <div class="flex-cell name d-none d-md-block">
                        {{ trans('courses.name') }}
                    </div>
                    <div class="flex-cell managers d-none d-md-block">
                        {{ trans('courses.managers') }}
                    </div>
                    <div class="flex-cell date d-none d-md-block">
                        {{ trans('courses.creation_date') }}
                    </div>
                    <div class="flex-cell description d-none d-md-block">
                        {{ trans('courses.description') }}
                    </div>
                </div>

                @foreach ($courses as $course)
                    @can('view', $course)
                        <div class="flex-row{{ $course->deleted_at ? ' deleted' : '' }} flex-wrap">
                            <div class="flex-cell name">
                                @if($course->deleted_at)
                                    <a
                                        href="{{ route('admin.courses.manage', ['filter' => App\Enums\CoursesFilter::Disabled]) }}"
                                        data-bs-toggle="tooltip"
                                        data-bs-custom-class="description-tooltip"
                                        data-bs-delay="300"
                                        title="{{ $course->name }}"
                                        class="lh-xs"
                                    >
                                        {{ $course->name }}
                                    </a>
                                @else
                                    <a
                                        href="{{ route('courses.show', $course->id) }}"
                                        data-bs-toggle="tooltip"
                                        data-bs-custom-class="description-tooltip"
                                        data-bs-delay="300"
                                        title="{{ $course->name }}"
                                        class="lh-xs"
                                    >
                                        {{ $course->name }}
                                    </a>
                                @endif
                            </div>
                            <div
                                class="flex-cell managers"
                                data-bs-toggle="tooltip"
                                title="{{ $course->managers(true)->implode('name', ', ') }}"
                            >
                                {{ $course->managers(true)->implode('name', ', ') }}
                            </div>
                            <div class="flex-cell date d-none d-md-block">
                                {{ $course->created_at ? $course->created_at->format('d/m/Y') : '' }}
                            </div>
                                <div
                                    class="flex-cell description @unless($course->description) d-none @endif"
                                    data-bs-toggle="tooltip"
                                    data-bs-custom-class="description-tooltip"
                                    title="{{ $course->description }}"
                                >
                                    {{ $course->description }}
                                </div>
                        </div>
                    @endcan
                @endforeach
            </div>
        @else
            <p class="text-secondary text-center">
                {{ trans('courses.not_found') }}
            </p>
        @endunless
    </div>
@endsection

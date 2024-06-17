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
                                    <a href="{{ route('admin.courses.manage', ['filter' => App\Enums\CoursesFilter::Disabled]) }}">
                                        {{ $course->name }}
                                    </a>
                                @else
                                    <a href="{{ route('courses.show', $course->id) }}">
                                        {{ $course->name }}
                                    </a>
                                @endif
                            </div>
                            @php($managers = $course->managers(true)->implode('name', ', '))
                            <div class="flex-cell managers @if(empty($managers)) d-none d-md-block @endif">
                                {{ $managers }}
                            </div>
                            <div class="flex-cell date d-none d-md-block">
                                {{ $course->created_at ? $course->created_at->format('d/m/Y') : '' }}
                            </div>
                            <div
                                class="line-clamp-2 flex-cell description @unless($course->description) d-none @endif"
                            >
                                <div class="pe-3">
                                    {{ $course->description }}
                                </div>
                                <div class="expand-button">
                                    <i class="fa-regular fa-square-plus"></i>
                                </div>
                                <div class="reduce-button">
                                    <i class="fa-regular fa-square-minus"></i>
                                </div>
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

@section('scripts-footer')
    <script>
        // Process expand button on description column.
        (function() {
            const isTextOverflowing = (element) => element.scrollHeight > element.clientHeight;

            const processExpandDescription = () => {
                document.querySelectorAll('.description').forEach((element) => {
                    element.classList.toggle(
                        'expandable',
                        isTextOverflowing(element),
                    );
                });
            };
            window.addEventListener('resize', () => processExpandDescription());
            processExpandDescription();

            document.querySelectorAll('.description').forEach(
                (element) => {
                    element.addEventListener('click', () => {

                        const isExpanded = element.classList.contains('line-clamp-2');

                        // Reduce all descriptions.
                        document.querySelectorAll('.description').forEach(
                            (parent) => parent.classList.add('line-clamp-2'),
                        );

                        // Expand or reduce the clicked description.
                        element.classList.toggle('line-clamp-2', !isExpanded);
                    });
                }
            );
        }());
    </script>
    @stack("scripts-boxes")
@endsection

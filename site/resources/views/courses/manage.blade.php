@extends('layouts.app-admin')

@section('admin.content')
    <div id="courses">
        <div class="card">
            <div class="card-header d-flex justify-content-between gap-2">
                <div class="title">
                    {{ trans('courses.manage') }}
                    <span class="badge bg-secondary">
                        {{ $courses->total() }}
                    </span>
                </div>
                <div class="header-actions d-flex gap-2 flex-wrap">
                    <div class="search-courses">
                        <form method="get" action="{{ route('admin.courses.manage') }}">
                            <div class="input-group">
                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="{{ trans('courses.search') }}"
                                       aria-label="{{ trans('courses.search') }}"
                                       aria-describedby="button-search-course"
                                       value="{{ $search }}">

                                @if($filter)
                                    <input type="hidden" name="filter" value="{{ $filter }}">
                                @endif

                                @if($search)
                                    <a class="btn bg-white border-top border-bottom"
                                       type="button"
                                       id="button-clear-course"
                                       href="{{ route('admin.courses.manage', ['filter' => $filter]) }}">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                @endif

                                <button class="btn{{ $search ? ' btn-primary' : ' btn-secondary'  }}"
                                        type="submit"
                                        id="button-search-course">
                                    {{ trans('general.search') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="filter-courses dropdown show">
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
                            <a class="dropdown-item" href="{{ route('admin.courses.manage', ['search' => $search]) }}">
                                -
                            </a>
                            <a class="dropdown-item"
                               href="{{ route('admin.courses.manage', ['filter' => \App\Enums\CoursesFilter::Disabled, 'search' => $search]) }}">
                                {!! Helpers::filterSelectedMark($filter, \App\Enums\CoursesFilter::Disabled) !!}
                                {{ trans('courses.disabled') }}
                            </a>
                            <a class="dropdown-item"
                               href="{{ route('admin.courses.manage', ['filter' => \App\Enums\CoursesFilter::External, 'search' => $search]) }}">
                                {!! Helpers::filterSelectedMark($filter, \App\Enums\CoursesFilter::External) !!}
                                {{ trans('courses.external') }}
                            </a>
                            <a class="dropdown-item"
                               href="{{ route('admin.courses.manage', ['filter' => \App\Enums\CoursesFilter::Orphan, 'search' => $search]) }}">
                                {!! Helpers::filterSelectedMark($filter, \App\Enums\CoursesFilter::Orphan) !!}
                                {{ trans('courses.orphan') }}
                            </a>
                            <a class="dropdown-item"
                               href="{{ route('admin.courses.manage', ['filter' => \App\Enums\CoursesFilter::Local, 'search' => $search]) }}">
                                {!! Helpers::filterSelectedMark($filter, \App\Enums\CoursesFilter::Local) !!}
                                {{ trans('courses.local') }}
                            </a>
                        </div>
                    </div>

                    <div class="create-courses">
                        <a href="{{ route('admin.courses.create') }}"
                           class="btn btn-primary">
                            {{ trans('courses.create') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive">
                @if ($courses->items())
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ trans('courses.name') }}</th>
                                <th>{{ trans('courses.type') }}</th>
                                <th>{{ trans('courses.details') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($courses->items() as $course)
                                @can('view', $course)
                                    <tr class="{{ $course->type }}{{ !$course->isActive() ? ' invalid' : '' }}">
                                        <td title="{{ $course->name }}">
                                            {{ Helpers::truncate($course->name, 60) }}
                                            @unless ($course->isActive())
                                                <span class="badge bg-danger">{{ trans('courses.disabled') }}</span>
                                            @endunless
                                            @if (Helpers::isCourseExternal($course))
                                                @if($course->orphan)
                                                    <div class="text-decoration-line-through text-secondary" title="{{ trans('courses.moodle_not_found', ['id' => $course->external_id]) }}">
                                                        {{ trans('courses.moodle_id', ['id' => $course->external_id]) }}
                                                    </div>
                                                @else
                                                    <div class="text-secondary">
                                                        {{ trans('courses.moodle_id', ['id' => $course->external_id]) }}
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            {{ Helpers::courseType($course->type) }}
                                        </td>
                                        <td>
                                            <div>{{ trans('cards.cards') }}: {{ $course->cards()->withTrashed()->count() }}</div>
                                            <div>{{ trans('enrollments.enrollments') }}: {{ $course->enrollments()->withTrashed()->count() }}</div>
                                        </td>
                                        <td class="actions">
                                            @unless($course->isActive())
                                                @can('mailConfirmDelete', $course)
                                                    <span>
                                                        <a href="{{ route('admin.courses.send.confirm.delete', $course->id) }}"
                                                           data-bs-toggle="tooltip"
                                                           data-placement="top"
                                                           class="btn btn-primary{{ $course->managers(true)->count() > 0 ? '' : ' disabled' }}"
                                                           title="{{ trans('courses.send_confirm_delete') }}">
                                                            <i class="far fa-paper-plane"></i>
                                                        </a>
                                                    </span>
                                                @endcan
                                                @can('enable', $course)
                                                    <span>
                                                        <a href="{{ route('admin.courses.enable', $course->id) }}"
                                                           data-bs-toggle="tooltip"
                                                           data-placement="top"
                                                           class="btn btn-success"
                                                           title="{{ trans('courses.enable') }}">
                                                            <i class="far fa-eye"></i>
                                                        </a>
                                                    </span>
                                                @endcan
                                            @else
                                                @can('update', $course)
                                                    <span>
                                                        <a href="{{ route('admin.courses.edit', $course->id) }}"
                                                           data-bs-toggle="tooltip"
                                                           data-placement="top"
                                                           class="btn btn-primary"
                                                           title="{{ trans('courses.edit') }}">
                                                            <i class="far fa-edit"></i>
                                                        </a>
                                                    </span>
                                                @endcan
                                                @can('disable', $course)
                                                    <span>
                                                        <form class="with-disable-confirm" method="post"
                                                              action="{{ route('courses.disable', $course->id) }}">
                                                            @method('DELETE')
                                                            @csrf
                                                            <input id="redirect" name="redirect" type="hidden" value="admin.courses.manage">
                                                            <button type="submit"
                                                                    class="btn btn-danger"
                                                                    data-bs-toggle="tooltip"
                                                                    data-placement="top"
                                                                    title="{{ trans('courses.disable') }}">
                                                                <i class="far fa-eye-slash"></i>
                                                            </button>
                                                        </form>
                                                    </span>
                                                @endcan
                                            @endunless

                                            @can('forceDelete', $course)
                                                <span>
                                                    <form class="with-delete-confirm" method="post"
                                                          action="{{ route('admin.courses.destroy', $course->id) }}">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-danger"
                                                                data-bs-toggle="tooltip"
                                                                data-placement="top"
                                                                title="{{ trans('courses.delete') }}">
                                                            <i class="far fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </span>
                                            @endcan
                                        </td>
                                    </tr>
                                @endcan
                            @endforeach
                        </tbody>
                    </table>
                    {{ $courses->onEachSide(1)->links() }}
                @else
                    <p class="text-secondary text-center">
                        {{ trans('courses.not_found') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app-admin')

@section('admin.content')
    <div id="courses">
        <div class="card">
            <div class="card-header">
                <span class="title">{{ trans('courses.manage') }} <span class="badge bg-secondary">{{ $courses->total() }}</span></span>
                <a href="{{ route('admin.courses.create') }}"
                   class="btn btn-primary float-end">
                    {{ trans('courses.create') }}
                </a>
                <div class="dropdown show float-end me-1">
                    <a class="btn btn-primary dropdown-toggle"
                       href="#"
                       role="button"
                       id="dropdownCoursesFiltersLink"
                       data-bs-toggle="dropdown"
                       aria-haspopup="true"
                       aria-expanded="false">
                        {{ trans('admin.filters') }}
                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownCoursesFiltersLink">
                        <a class="dropdown-item" href="{{ route('admin.courses.manage') }}">
                            -
                        </a>
                        <a class="dropdown-item"
                           href="{{ route('admin.courses.manage', ['filter' => \App\Enums\CoursesFilter::Disabled]) }}">
                            {{ trans('courses.disabled') }}
                        </a>
                        <a class="dropdown-item"
                           href="{{ route('admin.courses.manage', ['filter' => \App\Enums\CoursesFilter::External]) }}">
                            {{ trans('courses.external') }}
                        </a>
                        <a class="dropdown-item"
                           href="{{ route('admin.courses.manage', ['filter' => \App\Enums\CoursesFilter::Local]) }}">
                            {{ trans('courses.local') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
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
                                                <div class="text-secondary">{{ trans('courses.moodle_id', ['id' => $course->external_id]) }}</div>
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
                                            @can('update', $course)
                                                @if ($course->isActive())
                                                    <span>
                                                        <a href="{{ route('admin.courses.edit', $course->id) }}"
                                                           data-bs-toggle="tooltip"
                                                           data-placement="top"
                                                           class="btn btn-primary"
                                                           title="{{ trans('courses.edit') }}">
                                                            <i class="far fa-edit"></i>
                                                        </a>
                                                    </span>
                                                @endif
                                            @endcan

                                            @unless ($course->isActive())
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
                    <p class="text-secondary">
                        {{ trans('courses.not_found') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection

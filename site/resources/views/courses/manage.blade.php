@extends('layouts.app-admin')

@section('admin.content')
    <div id="courses">
        <div class="card">
            <div class="card-header">
                <span class="title">{{ trans('courses.manage') }} <span class="badge badge-secondary">{{ $courses->total() }}</span></span>
                <a href="{{ route('admin.courses.create') }}"
                   class="btn btn-primary float-right">
                    {{ trans('courses.create') }}
                </a>
            </div>
            <div class="card-body">
                @if ($courses->items())
                    <table class="table">
                        <thead>
                        <tr>
                            <th>{{ trans('courses.name') }}</th>
                            <th>{{ trans('courses.details') }}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($courses->items() as $course)
                            <tr class="{{ $course->deleted_at ? 'invalid' : '' }}">
                                <td>
                                    {{ $course->name }}
                                    @if ($course->deleted_at)
                                        <span class="badge badge-danger">{{ trans('courses.disabled') }}</span>
                                    @endif
                                </td>
                                <td>
                                    Cards: {{ $course->cards->count() }}
                                </td>
                                <td class="actions">
                                    @unless ($course->deleted_at)
                                        <span>
                                            <a href="{{ route('admin.courses.edit', $course->id) }}"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               class="btn btn-primary"
                                               title="{{ trans('courses.edit') }}">
                                                <i class="far fa-edit"></i>
                                            </a>
                                        </span>
                                    @endunless

                                    @if ($course->deleted_at)
                                        <span>
                                            <a href="{{ route('admin.courses.enable', $course->id) }}"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               class="btn btn-success"
                                               title="{{ trans('courses.enable') }}">
                                                <i class="far fa-eye"></i>
                                            </a>
                                        </span>
                                    @else
                                        <span>
                                            <form class="with-disable-confirm" method="post"
                                                  action="{{ route('courses.disable', $course->id) }}">
                                                @method('DELETE')
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-danger"
                                                        data-toggle="tooltip"
                                                        data-placement="top"
                                                        title="{{ trans('courses.disable') }}">
                                                    <i class="far fa-eye-slash"></i>
                                                </button>
                                            </form>
                                        </span>
                                    @endif

                                    <span>
                                        <form class="with-delete-confirm" method="post"
                                              action="{{ route('admin.courses.destroy', $course->id) }}">
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-danger"
                                                    data-toggle="tooltip"
                                                    data-placement="top"
                                                    title="{{ trans('courses.delete') }}">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{ $courses->onEachSide(1)->links() }}
                @else
                    <p class="text-secondary">
                        {{ trans('$courses.not_found') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection

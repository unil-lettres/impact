@extends('layouts.app-base')

@section('menu')
    @include('courses.menu')
@endsection

@section('content')
    <div id="configure-course">
        @section('title')
            {{ trans('courses.configure') }}
        @endsection
        @section('actions')
            <div class="float-end">
                @can('archive', $course)
                    <span class="me-1">
                        <form class="with-archive-confirm d-inline"
                            method="post"
                            action="{{ route('courses.archive', $course->id) }}">
                            @method('PUT')
                            @csrf
                            <button type="submit"
                                    class="btn btn-secondary"
                                    data-bs-toggle="tooltip"
                                    data-placement="top"
                                    title="{{ trans('messages.course.archive.info') }}">
                                <i class="far fa-folder-open"></i>
                                {{ trans('courses.archive') }}
                            </button>
                        </form>
                    </span>
                @endcan
                @can('disable', $course)
                    <span>
                        <form class="with-delete-confirm d-inline"
                            method="post"
                            action="{{ route('courses.disable', $course->id) }}">
                            @method('DELETE')
                            @csrf
                            <input id="redirect" name="redirect" type="hidden" value="home">
                            <button type="submit"
                                    class="btn btn-danger"
                                    data-bs-toggle="tooltip"
                                    data-placement="top"
                                    title="{{ trans('messages.course.delete.info') }}">
                                <i class="far fa-trash-alt"></i>
                                {{ trans('courses.delete') }}
                            </button>
                        </form>
                    </span>
                @endcan
            </div>
        @endsection
        <div class="clearfix"></div>
        <hr>

        @if ($errors->any())
            <div class="row">
                <div class="col">
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <span class="title">{{ trans('enrollments.enrollments') }}</span>
                    </div>
                    <div class="card-body">
                        {{ trans('enrollments.as_teacher') }}
                        <div id="rct-multi-user-teacher-select"
                             data='{{ json_encode(['record' => $course, 'role' => $teacherRole, 'options' => $users, 'defaults' => $usersAsTeacher, 'isDisabled' => Helpers::isCourseExternal($course)]) }}'
                        ></div>

                        <hr>

                        {{ trans('enrollments.as_student') }}
                        <div id="rct-multi-user-student-select"
                             data='{{ json_encode(['record' => $course, 'role' => $studentRole, 'options' => $users, 'defaults' => $usersAsStudent, 'isDisabled' => Helpers::isCourseExternal($course)]) }}'
                        ></div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="title">
                            {{ trans('courses.transcription.type') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <p>{{ trans('courses.transcription.type.help') }}</p>
                        <!-- TODO: add transcription type -->
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <span class="title">
                            {{ trans('courses.tags') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <p>{{ trans('courses.tags.help') }}</p>
                        <hr />
                        @can('create', [\App\Tag::class, $course])
                            <form method="post"
                                action="{{ route('tags.store') }}"
                                class="row row-cols-md-auto g-2">
                                @method('POST')
                                @csrf
                                <input type="hidden" name="course_id" value="{{ $course->id }}" >
                                <div class="col-12 flex-fill">
                                    <input
                                        name="name"
                                        type="text"
                                        class="form-control"
                                        placeholder="{{ trans('tags.create_placeholder') }}">
                                </div>
                                <div class="col-12">
                                    <button type="submit"
                                            class="btn btn-primary w-100"
                                            data-bs-toggle="tooltip"
                                            data-placement="top"
                                            title="{{ trans('tags.create') }}">
                                        {{ trans('tags.create') }}
                                    </button>
                                </div>
                            </form>
                            <hr />
                        @endcan
                        @if (count($tags) > 0)
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            <a href="{{route('courses.configure', ['course' => $course, 'tag_order' => 'name', 'tag_direction' => $tagColumns['name']])}}"
                                                class="icon-link link-dark link-underline-opacity-0">
                                                {{ trans("tags.name") }}
                                                <i class="fa-solid fa-sort-{{['asc' => 'down', 'desc' => 'up'][$tagColumns['name']]}}"></i>
                                            </a>
                                        </th>
                                        <th scope="col" class="text-end">
                                            <a href="{{route('courses.configure', ['course' => $course, 'tag_order' => 'cards_count', 'tag_direction' => $tagColumns['cards_count']])}}"
                                                class="icon-link link-dark link-underline-opacity-0">
                                                {{ trans("tags.cards_count") }}
                                                <i class="fa-solid fa-sort-{{['asc' => 'down', 'desc' => 'up'][$tagColumns['cards_count']]}}"></i>
                                            </a>
                                        </th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tags as $tag)
                                        <tr>
                                            <td>{{ $tag->name }}</td>
                                            <td class="text-end">{{ $tag->cards_count }}</td>
                                            <td>
                                                <div class="d-flex justify-content-end gap-1">
                                                    @can('update', $tag)
                                                        <span>
                                                            <a href=""
                                                            data-bs-toggle="tooltip"
                                                            data-placement="top"
                                                            class="btn btn-primary"
                                                            title="{{ trans('tags.edit') }}">
                                                                <i class="far fa-edit"></i>
                                                            </a>
                                                        </span>
                                                    @endcan
                                                    @can('delete', $tag)
                                                        <span>
                                                            <form class="with-delete-confirm" method="post"
                                                                action="{{ route('tags.destroy', ['tag' => $tag->id]) }}">
                                                                @method('DELETE')
                                                                @csrf
                                                                <button type="submit"
                                                                        class="btn btn-danger"
                                                                        data-bs-toggle="tooltip"
                                                                        data-placement="top"
                                                                        title="{{ trans('tags.delete') }}">
                                                                    <i class="far fa-trash-alt"></i>
                                                                </button>
                                                            </form>
                                                        </span>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div>{{ trans('tags.empty') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

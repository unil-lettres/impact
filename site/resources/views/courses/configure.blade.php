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
            @can('disable', $course)
                <span class="float-end">
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
            @can('archive', $course)
                <span class="float-end me-1">
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
        @endsection
        <hr>

        <div class="row">
            <div class="col-md-12 col-lg-6">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div><br />
                @endif
                <div class="card">
                    <div class="card-header">
                        <span class="title">
                            {{ trans('courses.tags') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <p>{{ trans('courses.tags.help') }}</p>
                        <x-forms.tags-manage :tags="$tags"></x-forms.tags-manage>
                    </div>
                </div>
            </div>

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
        </div>
    </div>
@endsection

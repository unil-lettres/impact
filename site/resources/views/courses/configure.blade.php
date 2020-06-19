@extends('layouts.app-base')

@section('menu')
    @include('courses.menu')
@stop

@section('content')
    <div id="configure-course">
        @can('archive', $course)
            <span>
                <form class="with-archive-confirm d-inline"
                      method="post"
                      action="{{ route('courses.archive', $course->id) }}">
                    @method('PUT')
                    @csrf
                    <button type="submit"
                            class="btn btn-secondary"
                            data-toggle="tooltip"
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
                    <button type="submit"
                            class="btn btn-danger"
                            data-toggle="tooltip"
                            data-placement="top"
                            title="{{ trans('messages.course.delete.info') }}">
                        <i class="far fa-trash-alt"></i>
                        {{ trans('courses.delete') }}
                    </button>
                </form>
            </span>
        @endcan
        <hr>
        <div class="row">
            <div class="col-md-12 col-lg-7">
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
                        <span class="title">{{ $course->name }}</span>
                    </div>
                    <div class="card-body">
                        // TODO: add transcription type & tags
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-lg-5">
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
            </div>
        </div>
    </div>
@endsection

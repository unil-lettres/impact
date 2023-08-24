@extends('layouts.app-base')

@section('menu')
    @include('courses.menu')
@endsection

@section('title')
    {{ trans('users.registrations') }}
@endsection

@section('content')
    @can('viewAny', [\App\User::class, $course])
        <div id="registrations">
            <div class="row">
                <div class="col-md-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <span class="title">{{ trans('enrollments.as_teacher') }}</span>
                        </div>
                        <div class="card-body">
                            <div id="rct-multi-user-teacher-select"
                                 data='{{ json_encode(['record' => $course, 'role' => $teacherRole, 'options' => $users, 'defaults' => $usersAsTeacher, 'isDisabled' => Helpers::isCourseExternal($course)]) }}'
                            ></div>
                            <div class="form-text">{{ trans('users.edit.enrollments_are_auto_save') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <span class="title">{{ trans('enrollments.as_student') }}</span>
                        </div>
                        <div class="card-body">
                            <div id="rct-multi-user-student-select"
                                 data='{{ json_encode(['record' => $course, 'role' => $studentRole, 'options' => $users, 'defaults' => $usersAsStudent, 'isDisabled' => Helpers::isCourseExternal($course)]) }}'
                            ></div>
                            <div class="form-text">{{ trans('users.edit.enrollments_are_auto_save') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

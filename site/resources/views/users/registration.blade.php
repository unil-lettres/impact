@extends('layouts.app-base')

@section('menu')
    @include('courses.menu')
@endsection

@section('content')
    @can('viewAny', [\App\User::class, $course])
        <div id="registrations">
            @section('title')
                {{ trans('users.registrations') }}
            @endsection
            <hr>

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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

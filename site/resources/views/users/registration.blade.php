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
                            <span class="title">{{ trans('enrollments.as_manager') }}</span>
                        </div>
                        <div class="card-body">
                            <div id="rct-multi-user-manager-select"
                                 data='{{ json_encode(['record' => $course, 'role' => $managerRole, 'options' => $users, 'defaults' => $usersAsManager, 'isDisabled' => Helpers::isCourseExternal($course)]) }}'
                            ></div>
                            <div class="form-text">{{ trans('users.edit.enrollments_are_auto_save') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <span class="title">{{ trans('enrollments.as_member') }}</span>
                        </div>
                        <div class="card-body">
                            <div id="rct-multi-user-member-select"
                                 data='{{ json_encode(['record' => $course, 'role' => $memberRole, 'options' => $users, 'defaults' => $usersAsMember, 'isDisabled' => Helpers::isCourseExternal($course)]) }}'
                            ></div>
                            <div class="form-text">{{ trans('users.edit.enrollments_are_auto_save') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

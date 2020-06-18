@extends('layouts.app-admin')

@section('admin.content')
    <div id="edit-course">
        @can('update', $course)
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
                            @unless (Helpers::isCourseLocal($course))
                                <span class="badge badge-secondary">{{ trans('courses.moodle_id', ['id' => $course->external_id]) }}</span>
                            @endunless
                            <form method="post"
                                  action="{{ route('admin.courses.update', $course->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label for="name" class="control-label">{{ trans('courses.name') }}</label>
                                    <div>
                                        <input type="text"
                                               id="name"
                                               name="name"
                                               value="{{ old('name', $course->name) }}"
                                               class="form-control"
                                               autofocus
                                        >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description">{{ trans('courses.description') }}</label>
                                    <textarea class="form-control"
                                              name="description"
                                              id="description"
                                              rows="3">{{ old('description', $course->description) }}</textarea>
                                </div>

                                <button type="submit"
                                        class="btn btn-primary">
                                    {{ trans('courses.update') }}
                                </button>
                            </form>
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
        @endcan
    </div>
@stop

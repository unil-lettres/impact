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
                                <span class="badge bg-secondary">{{ trans('courses.moodle_id', ['id' => $course->external_id]) }}</span>
                            @endunless
                            <form method="post"
                                  action="{{ route('admin.courses.update', $course->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="col-12 mb-3">
                                    <label for="name" class="control-label form-label">{{ trans('courses.name') }}</label>
                                    <div>
                                        <input type="text"
                                               id="name"
                                               name="name"
                                               @if(Helpers::isCourseExternal($course)) disabled @endif
                                               value="{{ old('name', $course->name) }}"
                                               class="form-control"
                                               autofocus
                                        >
                                    </div>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="description" class="form-label">{{ trans('courses.description') }}</label>
                                    <textarea class="form-control"
                                              name="description"
                                              id="description"
                                              @if(Helpers::isCourseExternal($course)) disabled @endif
                                              rows="3">{{ old('description', $course->description) }}</textarea>
                                </div>

                                @if(Helpers::isCourseLocal($course))
                                    <button type="submit"
                                            class="btn btn-primary">
                                        {{ trans('courses.update') }}
                                    </button>
                                @endif
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
                            <div class="form-text">{{ trans('users.edit.enrollments_are_auto_save') }}</div>
                            <hr>
                            {{ trans('enrollments.as_manager') }}
                            <div id="rct-multi-user-manager-select"
                                 class="mb-3"
                                 data='{{ json_encode(['record' => $course, 'role' => $managerRole, 'options' => $users, 'defaults' => $usersAsManager, 'isDisabled' => Helpers::isCourseExternal($course)]) }}'
                            ></div>
                            {{ trans('enrollments.as_member') }}
                            <div id="rct-multi-user-member-select"
                                 data='{{ json_encode(['record' => $course, 'role' => $memberRole, 'options' => $users, 'defaults' => $usersAsMember, 'isDisabled' => Helpers::isCourseExternal($course)]) }}'
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </div>
@endsection

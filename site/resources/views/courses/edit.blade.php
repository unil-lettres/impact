@extends('layouts.app-admin')

@section('admin.content')
    <div id="edit-course">
        @can('update', $course)
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
        @endcan
    </div>
@stop

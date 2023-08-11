@extends('layouts.app-base')

@section('menu')
    @include('courses.menu')
@endsection

@section('title')
    {{ trans('courses.configure') }}
@endsection

@canany(['archive', 'disable'], $course)
    @section('actions')
        @can('archive', $course)
            <form class="with-archive-confirm d-inline"
                    method="post"
                    action="{{ route('courses.archive', $course->id) }}">
                @method('PUT')
                @csrf
                <button type="submit"
                        class="btn btn-secondary me-1"
                        data-bs-toggle="tooltip"
                        data-placement="top"
                        title="{{ trans('messages.course.archive.info') }}">
                    <i class="far fa-folder-open"></i>
                    {{ trans('courses.archive') }}
                </button>
            </form>
        @endcan
        @can('disable', $course)
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
        @endcan
    @endsection
@endcanany

@section('content')
    <div id="configure-course">
        <form method="post"
              action="{{ route('courses.configure.update', $course->id) }}">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div><br />
            @endif

            <div class="row">
                <div class="col-md-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <span class="title">
                                {{ trans('courses.transcription.type') }}
                            </span>
                        </div>
                        <div class="card-body">
                            <label for="type">{{ trans('courses.transcription.type.help') }}</label>
                            <div class="mt-2">
                                <select id="type"
                                        name="type"
                                        class="form-control form-select" >
                                    <option value="{{ \App\Enums\TranscriptionType::Icor }}"
                                        {{ $course->transcription === \App\Enums\TranscriptionType::Icor ? 'selected' : '' }}>
                                        {{ Helpers::transcriptionTypeLabel(\App\Enums\TranscriptionType::Icor) }}
                                    </option>
                                    <option value="{{ \App\Enums\TranscriptionType::Text }}"
                                        {{ $course->transcription === \App\Enums\TranscriptionType::Text ? 'selected' : '' }}>
                                        {{ Helpers::transcriptionTypeLabel(\App\Enums\TranscriptionType::Text) }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                {{ trans('courses.configuration.update') }}
            </button>
        </form>
    </div>
@endsection

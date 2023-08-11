@extends('layouts.app-base')

@section('menu')
    @include('courses.menu')
@endsection

@section('title')
    {{ trans('states.states') }}
@endsection


@can('viewAny', [\App\State::class, $course])
    @can('create', [\App\State::class, $course])
        @section('actions')
            <form class="d-inline"
                    method="post"
                    action="{{ route('courses.create.state', $course->id) }}">
                @method('POST')
                @csrf
                <button type="submit"
                        class="btn btn-primary">
                    {{ trans('states.add') }}
                </button>
            </form>
        @endsection
    @endcan
    @section('content')
        <div id="states">
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
                <div class="col-md-12 col-lg-3">
                    <div class="card">
                        <div class="card-header">
                            <span class="title">
                                {{ trans('states.states') }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div id="states-list"
                                 course-id="{{ $course->id }}"
                                 class="list-group">
                                @foreach ($states as $state)
                                    <div state-id="{{ $state->id }}"
                                         class="list-group-item {{ $state->id == $activeState->id ? 'selected-bg ' : '' }} {{ !Helpers::isStateReadOnly($state) ? 'drag' : '' }}">
                                        <span class="align-middle">
                                            @can('view', $state)
                                                @if (Helpers::isStateReadOnly($state))
                                                    <i class="fa-solid fa-grip-lines me-1"></i>
                                                    <span class="text-muted">{{ $state->name }}</span>
                                                @else
                                                    <i class="fa-solid fa-bars me-1"></i>
                                                    <a class="legacy" href="{{ route('courses.configure.states', [$course->id, 'state' => $state->id]) }}">
                                                        {{ $state->name }}
                                                    </a>
                                                @endif
                                            @endcan
                                        </span>
                                        <span class="actions">
                                            @can('forceDelete', $state)
                                                <span class="float-end">
                                                    <form class="with-delete-confirm" method="post"
                                                          action="{{ route('courses.destroy.state', [$course->id, $state->id]) }}">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-sm btn-danger"
                                                                data-bs-toggle="tooltip"
                                                                data-placement="top"
                                                                {{ Helpers::isStateReferenced($state) ? 'disabled' : '' }}
                                                                title="{{ trans('states.delete') }}">
                                                            <i class="far fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </span>
                                            @endcan
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-9">
                    @if ($activeState)
                        <form method="post"
                              action="{{ route('courses.update.state', [$course->id, $activeState->id]) }}">
                            @csrf
                            @method('PUT')
                            <div class="card">
                                <div class="card-header">
                                    <span class="title">
                                        {{ trans('states.general') }}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="col-12 mb-3 row">
                                        <label for="name" class="col-md-3 col-form-label">
                                            {{ trans('states.name') }}
                                        </label>
                                        <div class="col-md-9">
                                            <input id="name"
                                                   type="text"
                                                   name="name"
                                                   value="{{ old('name', $activeState->name) }}"
                                                   class="form-control"
                                            >
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3 row">
                                        <label for="description" class="col-md-3 col-form-label">
                                            {{ trans('states.description') }}
                                        </label>
                                        <div class="col-md-9">
                                            <textarea class="form-control"
                                                      name="description"
                                                      id="description"
                                                      rows="3">{{ old('description', $activeState->description) }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3 row">
                                        <label for="teachers_only" class="col-md-3 col-form-label">
                                            {{ trans('states.teachers_only') }}
                                            <i class="far fa-question-circle"
                                               data-bs-toggle="tooltip"
                                               data-placement="top"
                                               title="{{ trans('states.teachers_only_help') }}">
                                            </i>
                                        </label>
                                        <div class="col-md-9">
                                            <div class="form-check">
                                                <input id="teachers_only"
                                                       type="checkbox"
                                                       name="teachers_only"
                                                       {{ old('teachers_only', $activeState->teachers_only) ? 'checked' : '' }}
                                                       class="form-check-input"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <span class="title">
                                        {{ trans('states.permissions') }}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="col-12 mb-3 row">
                                        <label for="box1" class="col-md-2 col-form-label">
                                            {{ trans('states.box1') }}
                                        </label>
                                        <div class="col-md-10">
                                            <select id="box1"
                                                    name="box1"
                                                    class="form-control form-select" >
                                                @include('states.include.permissions', ['box' => 'box1'])
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3 row">
                                        <label for="box2" class="col-md-2 col-form-label">
                                            {{ trans('states.box2') }}
                                        </label>
                                        <div class="col-md-10">
                                            <select id="box2"
                                                    name="box2"
                                                    class="form-control form-select" >
                                                @include('states.include.permissions', ['box' => 'box2'])
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3 row">
                                        <label for="box3" class="col-md-2 col-form-label">
                                            {{ trans('states.box3') }}
                                        </label>
                                        <div class="col-md-10">
                                            <select id="box3"
                                                    name="box3"
                                                    class="form-control form-select" >
                                                @include('states.include.permissions', ['box' => 'box3'])
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3 row">
                                        <label for="box4" class="col-md-2 col-form-label">
                                            {{ trans('states.box4') }}
                                        </label>
                                        <div class="col-md-10">
                                            <select id="box4"
                                                    name="box4"
                                                    class="form-control form-select" >
                                                @include('states.include.permissions', ['box' => 'box4'])
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3 row">
                                        <label for="box5" class="col-md-2 col-form-label">
                                            {{ trans('states.box5') }}
                                        </label>
                                        <div class="col-md-10">
                                            <select id="box5"
                                                    name="box5"
                                                    class="form-control form-select" >
                                                @include('states.include.permissions', ['box' => 'box5'])
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <span class="title">
                                        {{ trans('states.action') }}
                                    </span>

                                    <div class="col-md-4 col-6 float-end">
                                        <select id="action-type"
                                                name="action-type"
                                                class="form-control form-select"
                                                onchange="displayActionForm(this)">
                                            <option value="{{ \App\Enums\ActionType::None }}"
                                                {{ !Helpers::stateHasActions($activeState) ? 'selected' : '' }}>
                                                {{ trans('states.action_none') }}
                                            </option>
                                            <option value="{{ \App\Enums\ActionType::Email }}"
                                                {{ Helpers::stateHasActionOfType($activeState, \App\Enums\ActionType::Email) ? 'selected' : '' }}>
                                                {{ trans('states.action_email') }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="action-none {{ Helpers::stateHasActions($activeState) ? 'd-none' : ''  }}">
                                        <div class="text-center text-secondary">
                                            {{ trans('states.action_none_content') }}
                                        </div>
                                    </div>

                                    <div class="action-email {{ Helpers::stateHasActionOfType($activeState, \App\Enums\ActionType::Email) ? '' : 'd-none'  }}">
                                        @include('states.include.action-email')
                                    </div>
                                </div>
                            </div>

                            @can('update', $activeState)
                                <button type="submit"
                                        class="btn btn-primary"
                                        dusk="state-update-button">
                                    {{ trans('states.update') }}
                                </button>
                            @endcan
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endsection

    @section('scripts-footer')
        <script type="text/javascript">
            function displayActionForm(selectObject) {
                if (selectObject.value === "{{ \App\Enums\ActionType::None }}") {
                    $(".action-email:first").addClass('d-none');
                    $(".action-none:first").removeClass('d-none');
                } else if (selectObject.value === "{{ \App\Enums\ActionType::Email }}") {
                    $(".action-email:first").removeClass('d-none');
                    $(".action-none:first").addClass('d-none');
                }
            }
        </script>
    @endsection
@endcan

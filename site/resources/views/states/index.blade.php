@extends('layouts.app-base')

@section('menu')
    @include('courses.menu')
@endsection

@section('content')
    @can('viewAny', [\App\File::class, $course])
        <div id="states">
            @section('title')
                {{ trans('states.states') }}
            @endsection
            @section('actions')
            @endsection
            <hr>

            <div class="row">
                <div class="col-md-12 col-lg-3">
                    <div class="card">
                        <div class="card-header">
                            <span class="title">
                                <!-- TODO: add translation -->
                                États
                            </span>
                        </div>
                        <div class="card-body">
                            <ul>
                                @foreach ($states as $state)
                                    <li class="{{ $state->read_only ? 'text-muted' : '' }}">
                                        @if (!$state->read_only)
                                            <a href="{{ route('courses.configure.states', [$course->id, 'state' => $state->id]) }}"
                                               class="{{ $state->id == $activeState->id ? 'underline' : '' }}">
                                                {{ $state->name }}
                                            </a>
                                        @else
                                            {{ $state->name }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-9">
                    <div class="card">
                        <div class="card-header">
                            <span class="title">
                                <!-- TODO: add translation -->
                                Général
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label for="name" class="col-md-3 col-form-label">
                                    <!-- TODO: add translation -->
                                    Nom
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
                            <div class="form-group row">
                                <label for="description" class="col-md-3 col-form-label">
                                    <!-- TODO: add translation -->
                                    Description
                                </label>
                                <div class="col-md-9">
                                    <textarea class="form-control"
                                              name="description"
                                              id="description"
                                              rows="3">{{ old('description', $activeState->description) }}</textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="teachers_only" class="col-md-3 col-form-label">
                                    <!-- TODO: add translation -->
                                    Responsables uniquement
                                    <i class="far fa-question-circle"
                                       data-toggle="tooltip"
                                       data-placement="top"
                                       title="Seuls les responsables peuvent choisir cet état.">
                                        <!-- TODO: add translation -->
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
                                <!-- TODO: add translation -->
                                Permissions
                            </span>
                        </div>
                        <div class="card-body">
                            @if ($activeState)
                                <div>box1: {{ Helpers::permissionLabel($activeState->permissions['box1']) }}</div>
                                <div>box2: {{ Helpers::permissionLabel($activeState->permissions['box2']) }}</div>
                                <div>box3: {{ Helpers::permissionLabel($activeState->permissions['box3']) }}</div>
                                <div>box4: {{ Helpers::permissionLabel($activeState->permissions['box4']) }}</div>
                                <div>box5: {{ Helpers::permissionLabel($activeState->permissions['box5']) }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <span class="title">
                                <!-- TODO: add translation -->
                                Action
                            </span>
                        </div>
                        <div class="card-body">
                            <!-- TODO: add actions -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

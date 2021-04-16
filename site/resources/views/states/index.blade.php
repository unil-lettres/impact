@extends('layouts.app-base')

@section('menu')
    @include('courses.menu')
@endsection

@section('content')
    @can('viewAny', [\App\State::class, $course])
        <div id="states">
            @section('title')
                {{ trans('states.states') }}
            @endsection
            @section('actions')
                @can('create', [\App\State::class, $course])
                    <span class="float-right">
                        <form class="d-inline"
                              method="post"
                              action="{{ route('courses.create.state', $course->id) }}">
                            @method('POST')
                            @csrf
                            <button type="submit"
                                    class="btn btn-primary">
                                <!-- TODO: add translation -->
                                Ajouter un état
                            </button>
                        </form>
                    </span>
                @endcan
            @endsection
            <hr>

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
                                <!-- TODO: add translation -->
                                États
                            </span>
                        </div>
                        <div class="card-body">
                            <ul>
                                @foreach ($states as $state)
                                    @can('view', $state)
                                        @if (Helpers::isStateReadOnly($state))
                                            <li class="text-muted">
                                                {{ $state->name }}
                                            </li>
                                        @else
                                            <li>
                                                <a href="{{ route('courses.configure.states', [$course->id, 'state' => $state->id]) }}"
                                                   class="{{ $state->id == $activeState->id ? 'underline' : '' }}">
                                                    {{ $state->name }}
                                                </a>
                                            </li>
                                        @endif
                                    @endcan
                                @endforeach
                            </ul>
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
                                    <div class="form-group row">
                                        <!-- TODO: add translation -->
                                        <label for="box1" class="col-md-2 col-form-label">Box1</label>
                                        <div class="col-md-10">
                                            <select id="box1"
                                                    name="box1"
                                                    class="form-control" >
                                                @include('states.include.permissions', ['box' => 'box1'])
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <!-- TODO: add translation -->
                                        <label for="box2" class="col-md-2 col-form-label">Box2</label>
                                        <div class="col-md-10">
                                            <select id="box2"
                                                    name="box2"
                                                    class="form-control" >
                                                @include('states.include.permissions', ['box' => 'box2'])
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <!-- TODO: add translation -->
                                        <label for="box3" class="col-md-2 col-form-label">Box3</label>
                                        <div class="col-md-10">
                                            <select id="box3"
                                                    name="box3"
                                                    class="form-control" >
                                                @include('states.include.permissions', ['box' => 'box3'])
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <!-- TODO: add translation -->
                                        <label for="box4" class="col-md-2 col-form-label">Box4</label>
                                        <div class="col-md-10">
                                            <select id="box4"
                                                    name="box4"
                                                    class="form-control" >
                                                @include('states.include.permissions', ['box' => 'box4'])
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <!-- TODO: add translation -->
                                        <label for="box5" class="col-md-2 col-form-label">Box5</label>
                                        <div class="col-md-10">
                                            <select id="box5"
                                                    name="box5"
                                                    class="form-control" >
                                                @include('states.include.permissions', ['box' => 'box5'])
                                            </select>
                                        </div>
                                    </div>
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

                            @can('update', $activeState)
                                <button type="submit"
                                        class="btn btn-primary">
                                    <!-- TODO: add translation -->
                                    Mettre à jour l'état
                                </button>
                            @endcan
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endcan
@endsection

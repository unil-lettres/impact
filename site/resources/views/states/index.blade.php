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
                                {{ trans('states.add') }}
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
                                {{ trans('states.states') }}
                            </span>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tbody>
                                    @foreach ($states as $state)
                                        <tr class="{{ $state->id == $activeState->id ? 'selected-bg' : '' }}">
                                            <td class="align-middle">
                                                @can('view', $state)
                                                    @if (Helpers::isStateReadOnly($state))
                                                        <span class="text-muted">{{ $state->name }}</span>
                                                    @else
                                                        <a href="{{ route('courses.configure.states', [$course->id, 'state' => $state->id]) }}">
                                                            {{ $state->name }}
                                                        </a>
                                                    @endif
                                                @endcan
                                            </td>
                                            <td>
                                                @can('forceDelete', $state)
                                                    <span class="float-right">
                                                        <form class="with-delete-confirm" method="post"
                                                              action="{{ route('courses.destroy.state', [$course->id, $state->id]) }}">
                                                            @method('DELETE')
                                                            @csrf
                                                            <button type="submit"
                                                                    class="btn btn-sm btn-danger"
                                                                    data-toggle="tooltip"
                                                                    data-placement="top"
                                                                    title="{{ trans('states.delete') }}">
                                                                <i class="far fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </span>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
                                    <div class="form-group row">
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
                                    <div class="form-group row">
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
                                    <div class="form-group row">
                                        <label for="teachers_only" class="col-md-3 col-form-label">
                                            {{ trans('states.teachers_only') }}
                                            <i class="far fa-question-circle"
                                               data-toggle="tooltip"
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
                                    <div class="form-group row">
                                        <label for="box1" class="col-md-2 col-form-label">{{ trans('states.box1') }}</label>
                                        <div class="col-md-10">
                                            <select id="box1"
                                                    name="box1"
                                                    class="form-control" >
                                                @include('states.include.permissions', ['box' => 'box1'])
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="box2" class="col-md-2 col-form-label">{{ trans('states.box2') }}</label>
                                        <div class="col-md-10">
                                            <select id="box2"
                                                    name="box2"
                                                    class="form-control" >
                                                @include('states.include.permissions', ['box' => 'box2'])
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="box3" class="col-md-2 col-form-label">{{ trans('states.box3') }}</label>
                                        <div class="col-md-10">
                                            <select id="box3"
                                                    name="box3"
                                                    class="form-control" >
                                                @include('states.include.permissions', ['box' => 'box3'])
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="box4" class="col-md-2 col-form-label">{{ trans('states.box4') }}</label>
                                        <div class="col-md-10">
                                            <select id="box4"
                                                    name="box4"
                                                    class="form-control" >
                                                @include('states.include.permissions', ['box' => 'box4'])
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="box5" class="col-md-2 col-form-label">{{ trans('states.box5') }}</label>
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
                                        {{ trans('states.action') }}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <!-- TODO: add actions -->
                                </div>
                            </div>

                            @can('update', $activeState)
                                <button type="submit"
                                        class="btn btn-primary">
                                    {{ trans('states.update') }}
                                </button>
                            @endcan
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endcan
@endsection

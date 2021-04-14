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
                <div class="col-md-12 col-lg-4">
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
                                    <li class="{{ $state->read_only ? 'text-muted ' : 'pointer ' }}">
                                        @if ($state->id == $activeState->id)<u>@endif
                                            {{ $state->name }}
                                        @if ($state->id == $activeState->id)</u>@endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <span class="title">
                                <!-- TODO: add translation -->
                                Général
                            </span>
                        </div>
                        <div class="card-body">
                            <!-- TODO: add general -->
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

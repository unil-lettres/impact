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

            <ul>
                @foreach ($states as $states)
                    <li>{{ $states->name }}</li>
                @endforeach
            </ul>

            <!-- TODO: add states configuration ui -->
        </div>
    @endcan
@endsection

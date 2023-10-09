@extends('layouts.app-base')

@section('title')
    {{ $course->name }}
@endsection

@can('view', $course)
    @if (false
        || Auth::user()->can('configure', $course)
        || Auth::user()->can('create', [\App\Folder::class, $course])
        || Auth::user()->can('create', [\App\Card::class, $course])
    )
        @section('actions')
            @can('editConfiguration', $course)
                <a href="{{ route('courses.configure', $course->id) }}"
                   class="btn btn-primary">
                    {{ trans('courses.configure') }}
                </a>
            @endcan
            @can('create', [\App\Folder::class, $course])
                <button
                   class="btn btn-primary"
                   data-bs-toggle="modal"
                   data-bs-target="#modalCreate"
                   data-bs-type="{{('App\\Enums\\FinderRowType')::Folder}}"
                >
                   {{ trans('folders.create') }}
                </button>
            @endcan
            @can('create', [\App\Card::class, $course])
                <button
                   class="btn btn-primary"
                   data-bs-toggle="modal"
                   data-bs-target="#modalCreate"
                   data-bs-type="{{('App\\Enums\\FinderRowType')::Card}}"
                >
                    {{ trans('cards.create') }}
                </button>
            @endcan
        @endsection
    @endif
    @section('content')
        <livewire:finder
            :course="$course"
            modalCloneId="modalCloneIn"
            modalMoveId="modalMoveId"
            modalCreateId="modalCreate"
        />
    @endsection
@endcan

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
                   data-bs-target="#modalCreateFolder"
                >
                   {{ trans('folders.create') }}
                </button>
            @endcan
            @can('create', [\App\Card::class, $course])
                <button
                   class="btn btn-primary"
                   data-bs-toggle="modal"
                   data-bs-target="#modalCreateCard"
                >
                    {{ trans('cards.create') }}
                </button>
            @endcan
        @endsection
    @endif
    @section('content')
        <livewire:modal-create-folder
            id="modalCreateFolder"
            :course="$course"
        />
        <livewire:modal-create-card
            id="modalCreateCard"
            :course="$course"
        />
        <livewire:finder
            :course="$course"
            modalCloneId="modalCloneIn"
            modalMoveId="modalMoveIn"
            modalSetEditors="modalSetEditors"
        />
    @endsection
@endcan

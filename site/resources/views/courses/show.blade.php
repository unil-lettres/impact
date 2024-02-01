@extends('layouts.app-base')

@section('title')
    {{ $course->name }}
@endsection

@can('view', $course)
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
        <div class="dropdown" x-data="{ disabledPrint: true }">
            <button
                class="btn btn-primary"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                @click="disabledPrint = !document.getElementsByClassName('finder-card').length"
            >
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-with-icon">
                <li
                    x-data
                    class="dropdown-item d-flex cursor-pointer align-items-center"
                    :class="disabledPrint && 'disabled'"
                    data-trigger-print="{{ route('cards.print', ['course' => $course->id])}}"
                >
                    <i class="fa-solid fa-print me-2"></i>
                    <span class="flex-fill me-5">
                        {{ trans('courses.print')}}
                    </span>
                </li>
            </ul>
        </div>
    @endsection
    @section('content')
        <livewire:modal-create-folder
            id="modalCreateFolder"
            :course="$course"
        />
        <livewire:modal-create-card
            id="modalCreateCard"
            :course="$course"
        />
        <livewire:modal-update-state
            id="modalUpdateState"
            :course="$course"
        />
        <livewire:finder
            :course="$course"
            modalCloneId="modalCloneIn"
            modalMoveId="modalMoveIn"
        />
    @endsection
@endcan

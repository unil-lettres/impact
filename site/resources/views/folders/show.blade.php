@extends('layouts.app-base')

@section('title')
    <i class="fa-solid fa-folder"></i>
    {{ $folder->title }}
@endsection
@can('view', $folder)
    @section('actions')
        @can('editConfiguration', $folder->course)
            <a href="{{ route('courses.configure', $folder->course->id) }}"
                class="btn btn-primary">
                {{ trans('courses.configure') }}
            </a>
        @endcan
        @can('create', [\App\Folder::class, $folder->course])
            <button
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#modalCreateFolder"
            >
                {{ trans('folders.create') }}
            </button>
        @endcan
        @can('create', [\App\Card::class, $folder->course])
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
                @can('manage', $folder)
                <li
                    data-bs-toggle="modal"
                    data-bs-target="#modalMoveIn"
                    data-bs-keys="folder-{{$folder->id}}"
                    data-bs-reload
                    class="dropdown-item d-flex cursor-pointer align-items-center"
                >
                    <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>
                    <span class="flex-fill me-5">
                        {{ trans('folders.move') }}
                    </span>
                </li>
                <li
                    class="dropdown-item d-flex cursor-pointer align-items-center"
                    onClick="folderUtils.dispatchCustomEvent('finder-clone-folder', {folderId: {{$folder->id}}})"
                >
                    <i class="fa-solid fa-clone me-2"></i>
                    <span class="flex-fill me-5">
                        {{ trans('folders.copy')}}
                    </span>
                </li>
                <li
                    class="dropdown-item d-flex cursor-pointer align-items-center"
                    data-bs-toggle="modal"
                    data-bs-target="#modalCloneIn"
                    data-bs-keys="folder-{{$folder->id}}"
                >
                    <i class="fa-solid fa-file-import me-2"></i>
                    <span class="flex-fill me-5">
                        {{ trans('folders.clone_in')}}
                    </span>
                </li>
                <li
                    class="dropdown-item d-flex cursor-pointer align-items-center"
                    onClick="folderUtils.dispatchCustomEvent('finder-rename-folder', {folderId: {{$folder->id}}, title: '{{$folder->title}}'})"
                >
                    <i class="fa-solid fa-i-cursor me-2"></i>
                    <span class="flex-fill me-5">
                        {{ trans('folders.rename') }}
                    </span>
                </li>
                <li
                    onClick="folderUtils.destroyFolder({{$folder->id}})"
                    class="dropdown-item d-flex cursor-pointer align-items-center"
                >
                    <i class="fa-regular fa-trash-can me-2"></i>
                    <span class="flex-fill me-5">
                        {{ trans('folders.delete')}}
                    </span>
                </li>
                <li><hr class="dropdown-divider"></li>
                @endcan
                <li
                    class="dropdown-item d-flex cursor-pointer align-items-center"
                    data-trigger-print="{{ route('cards.print', ['folder' => $folder->id])}}"
                    :class="disabledPrint && 'disabled'"
                >
                    <i class="fa-solid fa-print me-2"></i>
                    <span class="flex-fill me-5">
                        {{ trans('folders.print')}}
                    </span>
                </li>
            </ul>
        </div>
    @endsection
    @section('content')
        <livewire:modal-create-folder
            id="modalCreateFolder"
            :course="$folder->course"
            :folder="$folder"
        />
        <livewire:modal-create-card
            id="modalCreateCard"
            :course="$folder->course"
            :folder="$folder"
        />
        <livewire:modal-update-state
            id="modalUpdateState"
            :course="$folder->course"
        />
        <livewire:finder
            :course="$folder->course"
            :folder="$folder"
            modalCloneId="modalCloneIn"
            modalMoveId="modalMoveIn"
        />
    @endsection
    @section('scripts-footer')
        <script>
            const folderUtils = {
                /**
                 * Helper for dispatching events that will be caught by Livewire finder.
                 */
                dispatchCustomEvent(eventName, args) {
                    const event = new CustomEvent(eventName, {
                        bubbles: true,
                        cancelable: false,
                        detail: { ...args }
                    });
                    window.dispatchEvent(event);
                },
                destroyFolder(folderId) {
                    if (confirm("{!! trans('courses.finder.menu.delete.folder.confirm') !!}")) {
                        this.dispatchCustomEvent('finder-destroy-folder', {folderId});
                    }
                }
            };
        </script>
    @endsection
@endcan

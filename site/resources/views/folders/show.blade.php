@extends('layouts.app-base')

@section('title')
    {{ $folder->title }}
@endsection
@can('view', $folder)
    @if (false
        || Auth::user()->can('configure', $folder->course)
        || Auth::user()->can('create', [\App\Folder::class, $folder->course])
        || Auth::user()->can('create', [\App\Card::class, $folder->course])
        || Auth::user()->can('update', $folder)
    )
        @section('actions')
            @can('editConfiguration', $folder->course)
                <a href="{{ route('courses.configure', $folder->course->id) }}"
                   class="btn btn-primary">
                    {{ trans('courses.configure') }}
                </a>
            @endcan
            @can('create', [\App\Folder::class, $folder->course])
                <a href="{{ route('folders.create', ['course' => $folder->course->id]) }}"
                   class="btn btn-primary">
                   {{ trans('folders.create') }}
                </a>
            @endcan
            @can('create', [\App\Card::class, $folder->course])
                <a href="{{ route('cards.create', ['course' => $folder->course->id]) }}"
                   class="btn btn-primary">
                    {{ trans('cards.create') }}
                </a>
            @endcan
            @can('update', $folder)
                <div class="dropdown">
                    <button class="btn btn-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-with-icon">
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
                                {{ trans('courses.finder.menu.copy')}}
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
                                {{ trans('courses.finder.menu.clone_in')}}
                            </span>
                        </li>
                        <li
                            class="dropdown-item d-flex cursor-pointer align-items-center"
                            onClick="folderUtils.dispatchCustomEvent('finder-rename-folder', {folderId: {{$folder->id}}})"
                        >
                            <i class="fa-solid fa-i-cursor me-2"></i>
                            <span class="flex-fill me-5">
                                {{ trans('folders.rename') }}
                            </span>
                        </li>
                        @can('forceDelete', $folder)
                            <li
                                onClick="folderUtils.destroyFolder({{$folder->id}})"
                                class="dropdown-item d-flex cursor-pointer align-items-center"
                            >
                                <i class="fa-regular fa-trash-can me-2"></i>
                                <span class="flex-fill me-5">
                                    {{ trans('courses.finder.menu.delete')}}
                                </span>
                            </li>
                        @endcan
                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-item d-flex cursor-pointer align-items-center">
                            <span class="flex-fill me-5">
                                {{ trans('courses.finder.menu.print')}}
                            </span>
                        </li>
                        <li class="dropdown-item d-flex cursor-pointer align-items-center">
                            <span class="flex-fill me-5">
                                {{ trans('courses.finder.menu.mail')}}
                            </span>
                        </li>
                    </ul>
                </div>
            @endcan
        @endsection
    @endif
    @section('content')
        <livewire:finder
            :course="$folder->course"
            :folder="$folder"
            modalCloneId="modalCloneIn"
            modalMoveId="modalMoveIn"
        />
        <!-- <div id="folder">
            <div>
                @include('shared.folders')
                @include('shared.cards')
            </div>
        </div> -->
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

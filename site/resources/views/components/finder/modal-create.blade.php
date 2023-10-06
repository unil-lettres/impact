@props(['id', 'course', 'folder' => null])

@php($children = $folder ? $folder->getChildrenRecursive() : $course->folders)

<div
    class="modal fade"
    x-data="{{$id}}"
    id="{{$id}}"
    tabindex="-1"
    aria-hidden="true"
    @click.stop
>
    <div class="modal-dialog">
        <div class="modal-content">
            <form wire:submit.prevent="createItem(name, type, folderId)">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" x-text="title"></h1>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="{{$id}}-name" class="form-label">
                            {{ trans('courses.name') }}
                        </label>
                        <input
                            id="{{$id}}-name"
                            type="text"
                            class="form-control"
                            x-model="name"
                            autocomplete="off"
                        />
                    </div>
                    <div class="mb-3">
                        <label for="{{$id}}-folder-id" class="control-label form-label">
                            {{ trans('folders.location') }}
                        </label>
                        <select
                            id="{{$id}}-folder-id"
                            class="form-select"
                            x-model="folderId"
                        >
                            <option value="{{$folder?->id}}">
                                @if ($folder)
                                    {{ $folder->title.' '.trans('folders.location.current') }}
                                @else
                                    {{ trans('courses.finder.dialog.rootFolder') }}
                                @endif
                            </option>
                            @foreach(Helpers::getFolderListAbsolutePath($children, $folder)->sortBy('titleFullPath') as $_folder)
                                <option value="{{$_folder->id}}">
                                    {{ $_folder->titleFullPath }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        {{ trans('courses.finder.dialog.create.cancel') }}
                    </button>
                    <button
                        data-bs-dismiss="modal"
                        type="submit"
                        class="btn btn-primary"
                    >
                        {{ trans('courses.finder.dialog.create.create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script data-navigate-once>
    document.addEventListener('livewire:init', () => {
        Alpine.data('{{$id}}', () => ({
            type: null,
            name: "",
            folderId: {{$folder->id ?? "null"}},
            get title() {
                return {
                    "{{('App\\Enums\\FinderRowType')::Folder}}": "{{ trans('folders.create') }}",
                    "{{('App\\Enums\\FinderRowType')::Card}}": "{{ trans('cards.create') }}"
                }[this.type];
            },
            init() {
                const modal = document.getElementById('{{$id}}');
                const inputName = document.getElementById('{{$id}}-name');
                modal.addEventListener('show.bs.modal', event => {
                    inputName.value = "";
                    this.folderId = {{$folder->id ?? "null"}};

                    const button = event.relatedTarget;
                    this.type = button.getAttribute('data-bs-type');

                    this.closeAllDropDowns();
                });
                modal.addEventListener('shown.bs.modal', event => {
                    inputName.focus();
                });
            },
        }));
    });
</script>

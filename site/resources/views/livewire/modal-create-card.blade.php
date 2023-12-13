<div
    class="modal fade"
    id="{{$id}}"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog">
        <div class="modal-content">
            <form wire:submit="create">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">{{ trans('cards.create') }}</h1>
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
                            wire:model="name"
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
                            wire:model="destination"
                        >
                            <option value="{{$folder?->id}}">
                                @if ($folder)
                                    {{ $folder->title.' '.trans('folders.location.current') }}
                                @else
                                    {{ trans('courses.finder.dialog.rootFolder') }}
                                @endif
                            </option>
                            @foreach($this->foldersDestination as $_folder)
                                <option value="{{$_folder->id}}">
                                    {{ $_folder->titleFullPath }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="{{$id}}-editors" class="control-label form-label">
                            {{ trans("cards.editors") }}
                        </label>
                        <div
                            wire:ignore
                            id="rct-multi-user-select"
                            data='{{ json_encode(['record' => $id.'-editors', 'options' => $this->enrolledUsers]) }}'
                            placeholder='{{ trans("messages.select.option") }}'
                            noOptionsMessage="{{ trans('messages.no.option') }}"
                        ></div>
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
        const modal = document.getElementById('{{$id}}');
        const inputName = document.getElementById('{{$id}}-name');
        modal.addEventListener('show.bs.modal', event => {
            // Reinitialize the editors react-select component every time
            // we open the modal, to prevent persisting old component
            // (with old values).
            window.MultiUserSelect.create();

            // Reset the editors property of the component, to prevent
            // persisting old values.
            @this.resetEditors(true);
        });
        modal.addEventListener('shown.bs.modal', event => {
            inputName.focus();
        });
    });
</script>

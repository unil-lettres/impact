@props(['id', 'course'])

<div
    class="modal fade"
    x-data="{{$id}}"
    id="{{$id}}"
    tabindex="-1"
    aria-hidden="true"
    @click.stop
    data-bs-backdrop="static"
>
    <div class="modal-dialog">
        <div class="modal-content">
            <form wire:submit.prevent="moveIn(keys, destFolder, reloadAfterSave)">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">
                        {{ trans('courses.finder.dialog.move_in.title') }}
                    </h1>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="{{$id}}-name" class="col-form-label">
                            {{ trans('courses.finder.dialog.move_in.prompt') }} :
                        </label>
                        <select
                            id="{{$id}}-name"
                            class="form-select"
                            x-model="_destFolder"
                            aria-label="move in destination folder"
                        >
                            <option value="">
                                {{ trans('courses.finder.dialog.rootFolder') }}
                            </option>
                            @foreach(Helpers::getFolderListAbsolutePath($course->folders)->sortBy('title') as $folder)
                                <option
                                    value="{{$folder->id}}"
                                    x-show="shouldShow('{{$folder->getAncestors()->pluck('id')->implode(',')}}')"
                                >
                                    {{ $folder->titleFullPath }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ trans('courses.finder.dialog.move_in.cancel') }}
                    </button>
                    <button
                        data-bs-dismiss="modal"
                        type="submit"
                        class="btn btn-primary"
                    >
                        {{ trans('courses.finder.dialog.move_in.accept') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('{{$id}}', () => ({
        // Contains the selected items on which the action should be performed.
        keys: [],
        _destFolder: null,
        reloadAfterSave: false,
        get destFolder() { return this._destFolder || null},
        init() {
            const modal = document.getElementById('{{$id}}');
            modal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                this.keys = button.getAttribute('data-bs-keys').split(',');
                this.reloadAfterSave = button.hasAttribute('data-bs-reload');
                this.closeAllDropDowns();
            });
        },
        /**
         * Return if the destination folder should be visible depending on
         * the keys. We don't want to move a folder into itself or into
         * one of its children.
         *
         * @param foldersId List of the folder with its parents that
         * should be checked.
         */
        shouldShow(foldersId) {
            const folders = foldersId.split(',');
            return !_.find(
                folders,
                folder => this.keys.includes(`folder-${folder}`),
            );
        }
    }));
</script>
@endscript

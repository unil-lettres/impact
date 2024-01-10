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
                    @include('livewire.modal-create-header', ['title' => trans('cards.create')])
                </div>
                <div class="modal-body">
                    @include('livewire.modal-create-common-fields')

                    <div class="mb-3">
                        <label for="{{$id}}-editors" class="control-label form-label">
                            {{ trans("cards.editors") }}
                        </label>
                        <div
                            wire:ignore
                            id="rct-multi-user-select"
                            data='{{ json_encode(['record' => $id.'-editors', 'options' => $this->enrolledUsers()]) }}'
                            placeholder='{{ trans("messages.select.option") }}'
                            noOptionsMessage="{{ trans('messages.no.option') }}"
                        ></div>
                    </div>
                </div>
                <div class="modal-footer">
                    @include('livewire.modal-create-footer')
                </div>
            </form>
        </div>
    </div>
</div>

@script
<script>
    const modal = document.getElementById('{{$id}}');
    const inputName = document.getElementById('{{$id}}-name');
    modal.addEventListener('show.bs.modal', event => {
        // Reinitialize the editors react-select component every time
        // we open the modal, to prevent persisting old component
        // (with old values).
        window.MultiEditorModalSelect.create();

        // Reset the editors property of the component, to prevent
        // persisting old values.
        $wire.resetEditors(true);
    });
    modal.addEventListener('shown.bs.modal', event => {
        inputName.focus();
    });
</script>
@endscript

<div
    class="modal fade"
    id="{{$id}}"
    tabindex="-1"
    aria-hidden="true"
    data-bs-backdrop="static"
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
                        <label for="{{$id}}-holders" class="control-label form-label">
                            {{ trans("cards.holders") }}
                        </label>
                        <div
                            wire:ignore
                            id="rct-multi-user-select"
                            data='{{ json_encode(['record' => $id.'-holders', 'options' => $this->enrolledUsers()]) }}'
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
    let modalIsOpen = false;

    modal.addEventListener('show.bs.modal', event => {
        modalIsOpen = true;
        // Reinitialize the holders react-select component every time
        // we open the modal, to prevent persisting old component
        // (with old values).
        window.MultiHolderModalSelect.create();

        // Reset the holders property of the component, to prevent
        // persisting old values.
        $wire.resetHolders(true);
    });
    modal.addEventListener('shown.bs.modal', event => {
        inputName.focus();
    });
    modal.addEventListener('hidden.bs.modal', () => {
        modalIsOpen = false;
    });

    $wire.on('close-modal', ({ id }) => {
        const modalEl = document.getElementById(id);
        const bsModal = bootstrap.Modal.getInstance(modalEl);
        if (bsModal) {
            bsModal.hide();
        }
    });

    // After Livewire re-renders (e.g. on validation failure), the DOM morph
    // strips Bootstrap's runtime .show class. Re-show the modal if it was open.
    Livewire.hook('morph.updated', ({ component }) => {
        if (component.id === $wire.__instance.id && modalIsOpen) {
            const modalEl = document.getElementById('{{$id}}');
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
        }
    });
</script>
@endscript

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
                    @include('livewire.modal-create-header', ['title' => trans('folders.create')])
                </div>
                <div class="modal-body">
                    @include('livewire.modal-create-common-fields')
                </div>
                <div class="modal-footer">
                    @include('livewire.modal-create-footer')
                </div>
            </form>
        </div>
    </div>
</div>

<script data-navigate-once>
    document.addEventListener('livewire:init', () => {
        const modal = document.getElementById('{{$id}}');
        const inputName = document.getElementById('{{$id}}-name');
        modal.addEventListener('shown.bs.modal', event => {
            inputName.focus();
        });
    });
</script>

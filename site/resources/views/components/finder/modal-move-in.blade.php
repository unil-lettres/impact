@props(['id'])

<div class="modal fade" x-data="{{$id}}" id="{{$id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">New message</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="recipient-name" class="col-form-label">Recipient:</label>
                        <input type="text" class="form-control" x-model="destFolder">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button
                        data-bs-dismiss="modal"
                        type="button"
                        wire:click="moveIn(keys, destFolder)"
                        class="btn btn-primary"
                    >
                        Send message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script data-navigate-once>
    document.addEventListener('livewire:init', () => {
        Alpine.data('{{$id}}', () => ({
            keys: [],
            destFolder: null,
            init() {
                const modal = document.getElementById('{{$id}}');
                modal.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;
                    this.keys = button.getAttribute('data-bs-keys');
                });
            }
        }));
    });
</script>

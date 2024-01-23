<div
    class="modal fade"
    tabindex="-1"
    aria-hidden="true"
    id="{{$id}}"
    x-data="modal"
>
    <div class="modal-dialog">
        <div class="modal-content">
            <form wire:submit="update">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">{{ trans("courses.finder.dialog.update_state.title") }}</h1>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="control-label form-label w-100">
                            {{ trans("cards.state") }}
                            <select wire:model="state" class="form-select">
                                    <option value="-1" class="empty-option" hidden="true"></option>
                                @foreach ($course->states as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        {{ trans('courses.finder.dialog.update_state.cancel') }}
                    </button>
                    <button
                        data-bs-dismiss="modal"
                        type="submit"
                        class="btn btn-primary"
                    >
                        {{ trans('courses.finder.dialog.update_state.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('modal', () => ({
        init() {
            this.$el.addEventListener('show.bs.modal', event => {
                const selectElement = this.$el.querySelector('select');
                const cardsId = event.relatedTarget.getAttribute('data-bs-cards').split(',');
                const stateId = event.relatedTarget.getAttribute('data-bs-state');

                $wire.init(cardsId, stateId);

                // We manually set the select value because of the skipRender
                // in init function.
                if (stateId === null) {
                    selectElement.value = -1;
                } else {
                    selectElement.value = stateId;
                }
            });
        },
    }));
</script>
@endscript

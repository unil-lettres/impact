@props(['id', 'course'])

@php($clonableCourses = Helpers::fetchCoursesAsTeacher(collect([$course])))

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
            <form @submit.prevent="submit">
                <input id="editors_ids" name="editors_ids" type="hidden" value="">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">
                        {{ trans('courses.finder.dialog.set_editors.title') }}
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
                        <label for="{{$id}}-editors" class="control-label form-label">
                            {{ trans("cards.editors") }}
                        </label>
                        <div
                            wire:ignore
                            id="rct-multi-editor-card-select"
                            placeholder='{{ trans("messages.select.option") }}'
                            noOptionsMessage="{{ trans('messages.no.option') }}"
                            reference="editors_ids"
                        ></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        {{ trans('courses.finder.dialog.set_editors.cancel') }}
                    </button>
                    <button
                        data-bs-dismiss="modal"
                        type="submit"
                        class="btn btn-primary"
                    >
                        {{ trans('courses.finder.dialog.set_editors.accept') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('{{$id}}', () => ({
        id: '{{$id}}',
        cardId: null,
        init() {
            const modal = document.getElementById('{{$id}}');

            modal.addEventListener('hidden.bs.modal', event => {
                // Unload react component to avoid seeing old values when
                // we open the modal again.
                window.MultiEditorCardSelect.destroy();
            });

            // MUST BE shown (and not show). If we initialize the react component
            // before it is visible, side effects will occurs (like not seeing
            // default values). It cause some kind of FOUC, or, like some people
            // say, a blip, but no other ways was found.
            modal.addEventListener('shown.bs.modal', event => {

                const button = event.relatedTarget;
                const editors = JSON.parse(button.getAttribute('data-bs-editors'));
                this.cardId = button.getAttribute('data-bs-card-id');

                // Dinamyically create the data from the card's editors list.
                const data = JSON.stringify({
                    record: this.id + '-editors',
                    options: {!!$this->enrolledUsers()!!},
                    defaults: editors,
                });
                window.MultiEditorCardSelect.create(data);

                this.closeAllDropDowns();
            });
        },
        submit() {
            console.log(document.getElementById('editors_ids').value);
            const editors = document.getElementById('editors_ids').value;
            $wire.changeEditors(this.cardId, editors);
        },
    }));
</script>
@endscript

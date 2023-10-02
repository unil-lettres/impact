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
                <div class="modal-header">
                    <h1 class="modal-title fs-5">
                        {{ trans('courses.finder.menu.clone_in.dialog.title') }}
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
                        <label for="recipient-name" class="col-form-label">
                            {{ trans('courses.finder.menu.clone_in.dialog.prompt') }} :
                        </label>
                        <select
                            id="recipient-name"
                            class="form-select"
                            x-model="destCourse"
                            size="8"
                            aria-label="copy in destination folder"
                        >
                            @foreach($clonableCourses as $clonableCourse)
                                <option
                                    value="{{$clonableCourse->id}}"
                                >
                                    {{$clonableCourse->name}}
                                </option>
                            @endforeach
                        </select>
                        <div id="recipient-name" class="form-text">
                        {{ trans('courses.finder.menu.clone_in.dialog.help') }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        {{ trans('courses.finder.menu.clone_in.dialog.cancel') }}
                    </button>
                    <button
                        data-bs-dismiss="modal"
                        type="button"
                        wire:click="cloneIn(keys, destCourse)"
                        class="btn btn-primary"
                    >
                        {{ trans('courses.finder.menu.clone_in.dialog.accept') }}
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
            destCourse: null,
            init() {
                const modal = document.getElementById('{{$id}}');
                modal.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;
                    this.keys = button.getAttribute('data-bs-keys').split(',');
                    this.closeAllDropDowns();
                });
            }
        }));
    });
</script>

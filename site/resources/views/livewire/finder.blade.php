<div class='finder' x-cloak>
    {{-- <div
        wire:loading.delay.longest
        class='modal-backdrop fade show'
    >
        <div
            class='d-flex justify-content-center align-items-center'
            style="height: 100%"
        >
            <div>
                <div
                    class='spinner-grow text-niagara'
                    role='status'
                >
                    <span class='visually-hidden'>Loading...</span>
                </div>
            </div>
        </div>
    </div>--}}
    <div>
        <div
            class="rct-multi-filter-select"
            data='{{ json_encode(['record' => 'tag', 'options' => $course->tags]) }}'
            placeholder='{{ trans("courses.finder.filter.tags") }}'
            wire:ignore
        ></div>
        <div
            class="rct-multi-filter-select"
            data='{{ json_encode(['record' => 'editor', 'options' => $this->editors]) }}'
            placeholder='{{ trans("courses.finder.filter.editors") }}'
            wire:ignore
        ></div>
    </div>
    <div class="d-flex row-height">
        <div class='column-large px-1'>
            <div {!! $this->sortAttributes('title') !!}>
                <div>{{ trans('courses.finder.name') }}</div>
                <div>
                    <i class="fa-solid fa-arrow-down"></i>
                    <i class="fa-solid fa-xmark d-none"></i>
                </div>
            </div>
        </div>
        <div class='column-small px-1 d-none d-sm-block'>
            <div {!! $this->sortAttributes('state_name') !!}>
                <div>{{ trans('courses.finder.state') }}</div>
                <div>
                    <i class="fa-solid fa-arrow-down"></i>
                    <i class="fa-solid fa-xmark d-none"></i>
                </div>
            </div>
        </div>
        <div class='column-small px-1 d-none d-xl-block'>
            <div {!! $this->sortAttributes('created_at') !!}>
                <div>{{ trans('courses.finder.created') }}</div>
                <div>
                    <i class="fa-solid fa-arrow-down"></i>
                    <i class="fa-solid fa-xmark d-none"></i>
                </div>
            </div>
        </div>
        <div class='column-medium px-1 d-none d-lg-block'>
            <div {!! $this->sortAttributes('editors_list') !!}>
                <div>{{ trans('courses.finder.editors') }}</div>
                <div>
                    <i class="fa-solid fa-arrow-down"></i>
                    <i class="fa-solid fa-xmark d-none"></i>
                </div>
            </div>
        </div>
        <div class='column-medium px-1 d-none d-lg-block'>
            <div {!! $this->sortAttributes('tags_list') !!}>
                <div>{{ trans('courses.finder.tags') }}</div>
                <div>
                    <i class="fa-solid fa-arrow-down"></i>
                    <i class="fa-solid fa-xmark d-none"></i>
                </div>
            </div>
        </div>
    </div>
    <ul
        class="finder-selectable-list"
        @click.outside="selectedItems = []"
        x-data="finderData"
        x-init="initSortable($el)"
    >
        @foreach ($this->rows as $row)
            @if ($row->getType() === ('App\\Enums\\FinderRowType')::Folder)
                <x-finder.folder
                    :folder="$row"
                    :sortColumn="$this->sortColumn"
                    :sortDirection="$this->sortDirection"
                    :lockedMove="$this->lockedMove"
                    :filters="$this->filters"
                />
            @else
                <x-finder.card :card="$row" :lockedMove="$this->lockedMove" />
            @endif
        @endforeach
    </ul>
    <div class="border-top border-secondary-subtle"></div>
    @section('scripts-footer')
        <script data-navigate-once>
            document.addEventListener('livewire:init', () => {
                Alpine.data('finderData', () => ({
                    selectedItems: [],
                    openedFolder: [],
                    toggleSelect(key) {
                        this.selectedItems = _.xor(this.selectedItems, [key]);
                    },
                    toggleOpen(element, key) {
                        this.openedFolder = _.xor(this.openedFolder, [key]);

                        // Unselect childs if folder is closed.
                        _.pull(
                            this.selectedItems,
                            ..._.map(
                                element.closest('li').querySelectorAll('li'),
                                childs => childs.getAttribute('data-key'),
                            ),
                        );
                    },
                    initSortable(list) {
                        Sortable.create(list, {
                            onStart: () => {
                                // Hide selected elements while dragging.
                                list.closest('.finder').classList.add('hide-select');
                            },
                            onEnd: () => {
                                // Display again selected elements when dragging end.
                                list.closest('.finder').classList.remove('hide-select');
                            },
                            onMove: (evt) => {
                                // Disable sorting when item has locked-move attribute.
                                return !evt.dragged.hasAttribute('locked-move');
                            },
                            onUpdate: (evt) => {
                                _.each(evt.item.parentNode.children, (row, index) => {
                                    row.dispatchEvent(new CustomEvent('sort-updated', {
                                        bubbles: true,
                                        cancelable: false,
                                        detail: {
                                            id: row.getAttribute('data-id'),
                                            type: row.getAttribute('data-type'),
                                            position: index,
                                        },
                                    }));
                                });
                            },
                            animation: 150,
                        });
                    }
                }));
            });
        </script>
    @endsection
</div>

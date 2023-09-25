<div class='finder' x-cloak x-data="finderData">
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
    <div class="toolsbox mt-3" style="height: 63px;">
        <div
            x-show.important="selectedItems.length === 0"
            class='d-flex gap-2'
            data-filter-label="{{ trans('courses.finder.filter_label') }}"
        >
            <div class="filter-select">
                <div
                    wire:ignore
                    class="rct-multi-filter-select"
                    data='{{ json_encode(['record' => 'tag', 'options' => $course->tags]) }}'
                    placeholder='{{ trans("courses.finder.filter.tags") }}'
                ></div>
            </div>
            <div class="filter-select">
                <div
                    wire:ignore
                    class="rct-multi-filter-select"
                    data='{{ json_encode(['record' => 'editor', 'options' => $this->editors]) }}'
                    placeholder='{{ trans("courses.finder.filter.editors") }}'
                ></div>
            </div>
            <div class="filter-select">
                <div
                    wire:ignore
                    class="rct-multi-filter-select"
                    data='{{ json_encode(['record' => 'state', 'options' => $course->states]) }}'
                    placeholder='{{ trans("courses.finder.filter.states") }}'
                ></div>
            </div>
            <div class="filter-select">
                <div
                    wire:ignore
                    id="rct-multi-filter-select-name"
                    createLabel="{{ trans('courses.finder.filter.names.create') }}"
                    noOptionsMessage="{{ trans('courses.finder.filter.names.empty') }}"
                    data='{{ json_encode(['record' => 'card', 'options' => collect([])]) }}'
                    placeholder='{{ trans("courses.finder.filter.names") }}'
                ></div>
                <div class="d-flex gap-2">
                    <div class="form-check border-end pe-2">
                        <label class="form-check-label" for="filterCardName">
                            Nom
                        </label>
                        <input
                            wire:click="toggleFilterCardDetail('name')"
                            class="form-check-input"
                            type="checkbox"
                            value=""
                            id="filterCardName">
                    </div>
                    <div>Case :</div>
                    <div class="form-check">
                        <input
                            wire:click="toggleFilterCardDetail('{{'App\\Enums\\CardBox'::Box2}}')"
                            class="form-check-input"
                            type="checkbox"
                            value=""
                            id="filterCardCase2">
                        <label class="form-check-label" for="filterCardCase2">
                            2
                        </label>
                    </div>
                    <div class="form-check">
                        <input
                            wire:click="toggleFilterCardDetail('{{'App\\Enums\\CardBox'::Box3}}')"
                            class="form-check-input"
                            type="checkbox"
                            value=""
                            id="filterCardCase3">
                        <label class="form-check-label" for="filterCardCase3">
                            3
                        </label>
                    </div>
                    <div class="form-check">
                        <input
                            wire:click="toggleFilterCardDetail('{{'App\\Enums\\CardBox'::Box4}}')"
                            class="form-check-input"
                            type="checkbox"
                            value=""
                            id="filterCardCase4">
                        <label class="form-check-label" for="filterCardCase4">
                            4
                        </label>
                    </div>
                </div>
            </div>
            <div class="text-nowrap">
                <button
                    class="btn"
                    wire:click="clearFilters"
                    @click="window.MultiFilterSelect.create()"
                >
                    {{ trans('courses.finder.filter.clear') }}
                </button>
            </div>
        </div>
        <div x-show="selectedItems.length > 0" class="bg-light rounded-pill px-3 py-1" @click.stop>
            <a href="#" class="me-2 text-body" @click="selectedItems = []"><i class="fa-solid fa-xmark"></i></a>
            <span>
                <strong x-text="selectedItems.length"></strong> {{ trans('courses.finder.selected') }}
                <strong x-text="selectedItems.filter(key => key.includes('card')).length"></strong> {{ trans('courses.finder.selected_cards') }}
            </span>
            <div class="dropdown d-inline-block" @click.stop>
                <button
                    class="btn border-0"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    @click.stop="openMenu($el, true)"
                    @dblclick.stop
                >
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                    <li class="dropdown-item d-flex cursor-pointer align-items-center">
                        <span class="flex-fill me-5">{{ trans('courses.finder.menu.move')}}</span>
                    </li>
                    <li class="dropdown-item d-flex cursor-pointer align-items-center">
                        <span class="flex-fill me-5">{{ trans('courses.finder.menu.copy')}}</span>
                    </li>
                    <li class="dropdown-item d-flex cursor-pointer align-items-center">
                        <span class="flex-fill me-5">{{ trans('courses.finder.menu.copy_in')}}</span>
                    </li>
                    <li wire:confirm="{{ trans('courses.finder.menu.delete.card.confirm') }}" wire:click="destroySelection()"
                        class="dropdown-item d-flex cursor-pointer align-items-center"
                    >
                        <i class="fa-regular fa-trash-can me-2"></i>
                        <span class="flex-fill me-5">{{ trans('courses.finder.menu.delete')}}</span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li class="dropdown-item d-flex cursor-pointer align-items-center">
                        <span class="flex-fill me-5">{{ trans('courses.finder.menu.print')}}</span>
                    </li>
                    <li class="dropdown-item d-flex cursor-pointer align-items-center">
                        <span class="flex-fill me-5">{{ trans('courses.finder.menu.mail')}}</span>
                    </li>
                </ul>
            </div>
            <button class="btn" @click="selectAll" x-show="!isAllSelected()">{{ trans('courses.finder.select_all')}}</button>
        </div>
    </div>
    <div class="d-flex row-height">
        <div class='flex-fill px-1'>
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
        <div class='column-options px-1'></div>
    </div>
    <ul
        class="finder-selectable-list"
        @click.outside="selectedItems = []"
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
                    toggleSelect(element, key) {

                        if (this.selectedItems.includes(key)) {

                            // Unselect all children of farthest selected parent
                            // of clicked element.
                            let parent = element.parentNode;
                            let farthestSelectedParent = null;

                            while (parent) {
                                if (parent?.classList?.contains('folder-selected')) {
                                    farthestSelectedParent = parent;
                                }
                                parent = parent.parentNode;
                            }

                            let _element = farthestSelectedParent || element;
                            let key = _element.getAttribute('data-key');
                            const keysToUnselect = _.map(
                                _element.querySelectorAll('.finder-folder, .finder-card'),
                                children => children.getAttribute('data-key'),
                            );
                            _.pull(this.selectedItems, ...keysToUnselect, key);
                        } else {
                            // Select clicked element and all its children.
                            const keysToSelect = _.map(
                                element.querySelectorAll('.finder-folder, .finder-card'),
                                children => children.getAttribute('data-key'),
                            );
                            this.selectedItems = _.uniq(
                                [...this.selectedItems, ...keysToSelect, key]
                            );
                        }

                        this.closeAllDropDowns(element);
                    },
                    toggleOpen(element, key) {
                        this.openedFolder = _.xor(this.openedFolder, [key]);

                        // Unselect children if folder is closed and while not selected.
                        if (!this.selectedItems.includes(key)) {
                            _.pull(
                                this.selectedItems,
                                ..._.map(
                                    element.closest('li').querySelectorAll('li'),
                                    childs => childs.getAttribute('data-key'),
                                ),
                            );
                        }

                        this.closeAllDropDowns(element);
                    },
                    openMenu(element, keepSelection = false) {
                        // Close all dropdowns except the one clicked.
                        // This is needed to be done manually because some
                        // events are not bubbled (preventPropagation).
                        this.closeAllDropDowns(element);

                        // Unselect items to avoid confusion on the targeted
                        // menu actions.
                        if (!keepSelection) this.selectedItems = [];
                    },
                    closeAllDropDowns(element) {
                        document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach((dropdown) => {
                            if (dropdown !== element) {
                                bootstrap.Dropdown.getInstance(dropdown)?.hide();
                            }
                        });
                    },
                    selectAll() {
                        this.selectedItems = _.map(
                            document.querySelectorAll('.finder-folder, .finder-card'),
                            children => children.getAttribute('data-key'),
                        );
                    },
                    isAllSelected() {
                        return this.selectedItems.length === document.querySelectorAll('.finder-folder, .finder-card').length;
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

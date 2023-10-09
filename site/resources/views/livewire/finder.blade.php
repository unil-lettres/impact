<div
    class='finder'
    x-cloak x-data="finderData"
    wire:toggle-filter-card-detail.window="toggleFilterCardDetail(...Object.values($event.detail))"
    wire:finder-destroy-folder.window="destroyFolder($event.detail.folderId, true)"
    wire:finder-clone-folder.window="cloneFolder($event.detail.folderId, true)"
    @finder-rename-folder.window="renameFolder($wire, $event.detail.folderId, true)"
    >
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div
            class="toast align-items-center {{session('bsClass')}} border-0 {{session('message') ? 'show' : 'hide'}}"
            role="alert"
            id="toast-flash"
            aria-live="assertive"
            aria-atomic="true"
        >
            <div class="d-flex">
                <div class="toast-body">
                {{ session('message') }}
                </div>
                <button
                    type="button"
                    class="btn-close btn-close-white me-2 m-auto"
                    @click="$el.closest('.toast').classList.remove('show')"
                    aria-label="Close"
                ></button>
            </div>
        </div>
    </div>
    <x-finder.modal-clone-in :id="$modalCloneId" :course="$course" />
    <x-finder.modal-move-in :id="$modalMoveId" :course="$course" />
    <x-finder.modal-create :id="$modalCreateId" :folder="$folder" :course="$course" />
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
                    data-name-label='{{ trans('courses.finder.name') }}'
                    data-box-label='{{ trans('courses.finder.filter.box') }}'
                ></div>
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
        <div x-show="selectedItems.length > 0" class="bg-light rounded-pill px-3 py-1 text-nowrap" @click.stop>
            <a href="#" class="me-2 text-body" @click="selectedItems = []"><i class="fa-solid fa-xmark"></i></a>
            <span>
                <strong x-text="selectedItems.length"></strong>
                {{ trans('courses.finder.selected') }}
                <strong x-text="selectedItems.filter(key => key.includes('card')).length"></strong>
                {{ trans('courses.finder.selected_cards') }}
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
                    <li
                        class="dropdown-item d-flex cursor-pointer align-items-center d-flex d-sm-none"
                        @click="selectAll"
                    >
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.select_all')}}
                        </span>
                    </li>1
                    <li class="d-block d-sm-none">
                        <hr class="dropdown-divider">
                    </li>
                    <li
                        class="dropdown-item d-flex cursor-pointer align-items-center"
                        data-bs-toggle="modal"
                        data-bs-target="#modalMoveIn"
                        :data-bs-keys="selectedItems"
                    >
                        <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.move_in')}}
                        </span>
                    </li>
                    <li
                        class="dropdown-item d-flex cursor-pointer align-items-center"
                        wire:click="cloneMultiple(selectedItems)"
                    >
                        <i class="fa-solid fa-clone me-2"></i>
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.menu.copy')}}
                        </span>
                    </li>
                    <li
                        class="dropdown-item d-flex cursor-pointer align-items-center"
                        data-bs-toggle="modal"
                        data-bs-target="#modalCloneIn"
                        :data-bs-keys="selectedItems"
                    >
                        <i class="fa-solid fa-file-import me-2"></i>
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.clone_in')}}
                        </span>
                    </li>
                    <li
                        wire:confirm="{{ trans('courses.finder.menu.delete.card.confirm') }}"
                        wire:click="destroyMultiple(selectedItems)"
                        class="dropdown-item d-flex cursor-pointer align-items-center"
                    >
                        <i class="fa-regular fa-trash-can me-2"></i>
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.menu.delete')}}
                        </span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li class="dropdown-item d-flex cursor-pointer align-items-center">
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.menu.print')}}
                        </span>
                    </li>
                    <li class="dropdown-item d-flex cursor-pointer align-items-center">
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.menu.mail')}}
                        </span>
                    </li>
                </ul>
            </div>
            <button class="btn d-none d-sm-inline-block" @click="selectAll" x-show.important="!isAllSelected()">
                {{ trans('courses.finder.select_all')}}
            </button>
            <div class="btn d-none d-lg-inline-block text-secondary">
                {{ trans('courses.finder.select.help')}}
            </div>
        </div>
    </div>
    <div class="d-flex row-height">
        <div class='flex-fill px-1'>
            <div class="d-flex">
                <div {!! $this->sortAttributes('title') !!}>
                    <div>{{ trans('courses.finder.name') }}</div>
                    <div>
                        <i class="fa-solid fa-arrow-down"></i>
                        <i class="fa-solid fa-xmark d-none"></i>
                    </div>
                </div>
                <button class="btn ms-3" @click="expandAll()">
                    {{ trans('courses.finder.expand_all') }}
                </button>
                <button class="btn" @click="openedFolder = []">
                {{ trans('courses.finder.collapse_all') }}
                </button>
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
        @forelse ($this->rows as $row)
            @if ($row->getType() === ('App\\Enums\\FinderRowType')::Folder)
                <x-finder.folder
                    :folder="$row"
                    :sortColumn="$this->sortColumn"
                    :sortDirection="$this->sortDirection"
                    :lockedMove="$this->lockedMove"
                    :filters="$this->filters"
                    :modalCloneId="$modalCloneId"
                    :modalMoveId="$modalMoveId"
                />
            @else
                <x-finder.card
                    :card="$row"
                    :lockedMove="$this->lockedMove"
                    :modalCloneId="$modalCloneId"
                    :modalMoveId="$modalMoveId"
                />
            @endif
        @empty
            <li class="text-center finder-folder border-top border-secondary-subtle row-height cursor-default">
                {{trans('courses.finder.empty')}}
            </li>
        @endforelse
    </ul>
    <div class="border-top border-secondary-subtle"></div>
    <script data-navigate-once>
        document.addEventListener('livewire:init', () => {

            Alpine.data('finderData', () => ({
                selectedItems: [],
                lastSelectedItem: null,
                openedFolder: [],
                toggleSelect(event, element, fromShiftSelection = false) {

                    const key = element.getAttribute('data-key')

                    if (!fromShiftSelection && this.selectedItems.includes(key)) {

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
                            child => child.getAttribute('data-key'),
                        );
                        this.selectedItems = _.uniq(
                            [...this.selectedItems, ...keysToSelect, key]
                        );

                        // Select all elements between last selected item and
                        // current selected item if shift key is pressed.
                        if (!fromShiftSelection && event.shiftKey) {
                            const lastSelectedElement = element.parentNode.querySelector(
                                `:scope > [data-key="${this.lastSelectedItem}"]`
                            );

                            // Shift selection works only with elements in the
                            // same folder.
                            if (lastSelectedElement) {
                                let start = false;

                                // Select all element between last selected item
                                // and current selected item.
                                _.each(element.parentNode.children, child => {
                                    if (child === element || child === lastSelectedElement) {
                                        start = !start;
                                    }
                                    if (start) {
                                        this.toggleSelect(
                                            event,
                                            child,
                                            child.getAttribute('data-key'),
                                            true,
                                        );
                                    }
                                });
                            } else {
                                this.lastSelectedItem = key;
                            }
                        } else {
                            this.lastSelectedItem = key;
                        }
                    }

                    this.closeAllDropDowns(element);
                },
                expandAll() {
                    _.each(
                        document.querySelectorAll('.finder-folder'),
                        element => {
                            const key = element.getAttribute("data-key");
                            if (!this.openedFolder.includes(key)) {
                                this.openedFolder.push(key);
                            }
                        },
                    );
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
                closeAllDropDowns(element = null) {
                    document.querySelectorAll(
                        '[data-bs-toggle="dropdown"]',
                    ).forEach((dropdown) => {
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
                renameFolder($wire, folderId, reloadAfterSave = false) {
                    const newName = prompt("{{ trans('courses.finder.menu.rename_prompt') }}");
                    if (newName !== null) {
                        $wire.call("renameFolder", folderId, newName, reloadAfterSave);
                    }
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
</div>

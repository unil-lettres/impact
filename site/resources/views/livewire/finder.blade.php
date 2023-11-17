<div
    class='finder position-relative'
    x-cloak x-data="finderData"
    wire:toggle-filter-search-box.window="toggleFilterSearchBox(...Object.values($event.detail))"
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
                {!! session('message') !!}
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
                    noOptionsMessage="{{ trans('courses.finder.filter.empty') }}"
                    data='{{ json_encode(['record' => 'tag', 'options' => $course->tags, 'defaults' => $this->filters->get("tag")->map(fn ($id) => 'App\\Tag'::find($id))->toArray()]) }}'
                    placeholder='{{ trans("courses.finder.filter.tags") }}'
                ></div>
            </div>
            <div class="filter-select">
                <div
                    wire:ignore
                    class="rct-multi-filter-select"
                    noOptionsMessage="{{ trans('courses.finder.filter.empty') }}"
                    data='{{ json_encode(['record' => 'editor', 'options' => $this->editors, 'defaults' => $this->filters->get("editor")->map(fn ($id) => 'App\\User'::find($id))->toArray()]) }}'
                    placeholder='{{ trans("courses.finder.filter.editors") }}'
                ></div>
            </div>
            <div class="filter-select">
                <div
                    wire:ignore
                    class="rct-multi-filter-select"
                    noOptionsMessage="{{ trans('courses.finder.filter.empty') }}"
                    data='{{ json_encode(['record' => 'state', 'options' => $course->states->sortBy('position')->values(), 'defaults' => $this->filters->get("state")->map(fn ($id) => 'App\\State'::find($id))->toArray()]) }}'
                    placeholder='{{ trans("courses.finder.filter.states") }}'
                ></div>
            </div>
            <div class="filter-select">
                <div
                    wire:ignore
                    id="rct-multi-filter-select-name"
                    createLabel="{{ trans('courses.finder.filter.names.create') }}"
                    noOptionsMessage="{{ trans('courses.finder.filter.names.empty') }}"
                    data='{{ json_encode(['record' => 'search', 'options' => $this->filterSearchOptions(), 'defaults' => $this->filterSearchOptions()]) }}'
                    placeholder='{{ trans("courses.finder.filter.names") }}'
                    data-name-label='{{ trans('courses.finder.name') }}'
                    data-box-label='{{ trans('courses.finder.filter.box') }}'
                ></div>
            </div>
            <div class="text-nowrap">
                <button
                    class="btn"
                    wire:click="clearFiltersAndSort"
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
                    </li>
                    <li class="d-block d-sm-none">
                        <hr class="dropdown-divider">
                    </li>
                    @can('massActionsForCardAndFolder', $course)
                    <li
                        class="dropdown-item d-flex cursor-pointer align-items-center"
                        data-bs-toggle="modal"
                        data-bs-target='{{"#$modalMoveId"}}'
                        :data-bs-keys="selectedItems"
                        dusk="multi-movein-option"
                    >
                        <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.move_in')}}
                        </span>
                    </li>
                    <li
                        class="dropdown-item d-flex cursor-pointer align-items-center"
                        wire:click="cloneMultiple(selectedItems)"
                        dusk="multi-copy-option"
                    >
                        <i class="fa-solid fa-clone me-2"></i>
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.menu.copy')}}
                        </span>
                    </li>
                    <li
                        class="dropdown-item d-flex cursor-pointer align-items-center"
                        data-bs-toggle="modal"
                        data-bs-target='{{"#$modalCloneId"}}'
                        :data-bs-keys="selectedItems"
                        dusk="multi-clonein-option"
                    >
                        <i class="fa-solid fa-file-import me-2"></i>
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.clone_in')}}
                        </span>
                    </li>
                    <li
                        wire:confirm="{{ trans('courses.finder.menu.delete.all.confirm') }}"
                        wire:click="destroyMultiple(selectedItems)"
                        class="dropdown-item d-flex cursor-pointer align-items-center"
                        dusk="multi-delete-option"
                    >
                        <i class="fa-regular fa-trash-can me-2"></i>
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.menu.delete')}}
                        </span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    @endcan
                    <li class="dropdown-item d-flex cursor-pointer align-items-center">
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.menu.print')}}
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
                <div {!! Helpers::finderSortHTMLAttributes('title', $this->sortColumn, $this->sortDirection) !!}>
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
            <div {!! Helpers::finderSortHTMLAttributes('state_name', $this->sortColumn, $this->sortDirection) !!}>
                <div>{{ trans('courses.finder.state') }}</div>
                <div>
                    <i class="fa-solid fa-arrow-down"></i>
                    <i class="fa-solid fa-xmark d-none"></i>
                </div>
            </div>
        </div>
        <div class='column-small px-1 d-none d-xl-block'>
            <div {!! Helpers::finderSortHTMLAttributes('created_at', $this->sortColumn, $this->sortDirection) !!}>
                <div>{{ trans('courses.finder.created') }}</div>
                <div>
                    <i class="fa-solid fa-arrow-down"></i>
                    <i class="fa-solid fa-xmark d-none"></i>
                </div>
            </div>
        </div>
        <div class='column-medium px-1 d-none d-lg-block'>
            <div {!! Helpers::finderSortHTMLAttributes('editors_list', $this->sortColumn, $this->sortDirection) !!}>
                <div>{{ trans('courses.finder.editors') }}</div>
                <div>
                    <i class="fa-solid fa-arrow-down"></i>
                    <i class="fa-solid fa-xmark d-none"></i>
                </div>
            </div>
        </div>
        <div class='column-medium px-1 d-none d-lg-block'>
            <div {!! Helpers::finderSortHTMLAttributes('tags_list', $this->sortColumn, $this->sortDirection) !!}>
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
        @forelse ($this->items as $item)
            @if ($item->getFinderItemType() === ('App\\Enums\\FinderItemType')::Folder)
                <x-finder.folder
                    :folder="$item"
                    :locked-move="$this->lockedMove"
                    :$sortColumn
                    :$sortDirection
                    :filters="$this->filters"
                    :$filterSearchBoxes
                    :$modalCloneId
                    :$modalMoveId
                />
            @else
                <x-finder.card
                    :card="$item"
                    :locked-move="$this->lockedMove"
                    :$modalCloneId
                    :$modalMoveId
                />
            @endif
        @empty
            <li class="text-center finder-folder border-top border-secondary-subtle row-height cursor-default">
                {{trans('courses.finder.empty')}}
            </li>
        @endforelse
    </ul>
    <div class="border-top border-secondary-subtle"></div>
    <div
        class="bg-secondary-subtle opacity-50 z-index-backdrop position-absolute top-0 start-0 bottom-0 end-0"
        wire:loading.delay.longer
    >
        <div
            class="spinner-grow text-niagara position-absolute top-50 start-50 translate-middle"
            role="status"
        >
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    @include('livewire.finder-js')
</div>

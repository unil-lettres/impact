<li
    class="finder-folder border-top border-secondary-subtle row-height cursor-default"
    :class="!selectedItems.includes(key) || 'folder-selected'"
    data-id="{{ $folder->id }}"
    data-type="{{ $folder->getFinderItemType() }}"
    dusk="finder-folder-{{ $folder->id }}"
    x-data="{ key: '{{ $folder->getFinderItemType() }}-{{ $folder->id }}', mouseover: false}"
    :data-key="key"
    @mouseover.stop="mouseover = true"
    @mouseout.stop="mouseover = false"
    @click.stop="toggleSelect($event, $el)"
    wire:key='{{ $folder->getFinderItemType() }}-{{ $folder->id }}'
    {{ $lockedMove ? 'locked-move' : '' }}
>
    <div
        class="d-flex background-hover"
        :class="!selectedItems.includes(key) || 'selected'"
        title="{{ $folder->title }}"
    >
        <div class='flex-fill text-truncate px-1'>
            <input
                class="opacity-0"
                :class="(!selectedItems.includes(key) && !mouseover) || 'opacity-100'"
                type="checkbox"
                :checked="selectedItems.includes(key)"
            />
            <div
                class="d-inline-block cursor-pointer"
                @click.stop="toggleOpen($el, key)"
            >

                @for ($i = 0; $i < $depth; $i++)
                    <i class="d-inline-block width-small">&nbsp;</i>
                @endfor
                @if ($items->count() > 0)
                    <i
                        class="fa-solid fa-caret-down d-inline-block text-center width-small transition-transform"
                        :class="openedFolder.includes(key) || 'rotate'"
                    ></i>
                @else
                    <i class="d-inline-block width-small">&nbsp;</i>
                @endif
                <i
                    class="d-inline-block text-center width-large"
                    :class="openedFolder.includes(key) ? 'fa-regular fa-folder-open' : 'fa-solid fa-folder'">
                </i>
            </div>
            <a class="legacy text-black" href="#" wire:click.stop="openFolder({{$folder->id}})">
                {{ $folder->title }}
            </a>
        </div>
        <div class="text-secondary text-nowrap">
            {{ $countCards }}
            {{ trans('courses.finder.folder.cards_count')}}
        </div>
        <div class='column-options'>
            <div class="dropdown" @click.stop>
                <button
                    class="btn border-0 text-black"
                    :class="selectedItems.length > 1 ? 'text-secondary' : ''"
                    style="width:100%"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    @click.stop="openMenu($el)"
                >
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-with-icon">
                    <li
                        class="dropdown-item d-flex cursor-pointer align-items-center"
                        wire:click="openFolder({{$folder->id}})"
                    >
                        <i class="fa-solid fa-square-arrow-up-right me-2"></i>
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.menu.open')}}
                        </span>
                    </li>
                    <li
                        class="dropdown-item d-flex cursor-pointer align-items-center"
                        x-show.important="!openedFolder.includes(key)"
                        @click="toggleOpen($el, key)"
                    >
                        <i class="d-inline-block text-center width-large fa-regular fa-folder-open me-2"></i>
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.menu.folder.expand')}}
                        </span>
                        <span class="text-secondary ms-3 text-lowercase fw-light">
                            {{ trans('courses.finder.menu.folder.expand.help')}}
                        </span>
                    </li>
                    <li
                        class="dropdown-item d-flex cursor-pointer align-items-center"
                        x-show.important="openedFolder.includes(key)"
                        @click="toggleOpen($el, key)"
                    >
                        <i class="fa-solid fa-folder me-2"></i>
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.menu.folder.collapse')}}
                        </span>
                        <span class="text-secondary ms-3 text-lowercase fw-light">
                            {{ trans('courses.finder.menu.folder.expand.help')}}
                        </span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    @can('editConfiguration', $folder->course)
                        <li
                            class="dropdown-item d-flex cursor-pointer align-items-center"
                            data-bs-toggle="modal"
                            data-bs-target="#{{$modalMoveId}}"
                            :data-bs-keys="[key]"
                        >
                            <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>
                            <span class="flex-fill me-5">
                                {{ trans('courses.finder.move_in')}}
                            </span>
                        </li>
                        <li
                            class="dropdown-item d-flex cursor-pointer align-items-center"
                            wire:click="cloneFolder({{$folder->id}})"
                        >
                            <i class="fa-solid fa-clone me-2"></i>
                            <span class="flex-fill me-5">
                                {{ trans('courses.finder.menu.copy')}}
                            </span>
                        </li>
                        <li
                            class="dropdown-item d-flex cursor-pointer align-items-center"
                            data-bs-toggle="modal"
                            data-bs-target="#{{$modalCloneId}}"
                            :data-bs-keys="[key]"
                        >
                            <i class="fa-solid fa-file-import me-2"></i>
                            <span class="flex-fill me-5">
                                {{ trans('courses.finder.clone_in')}}
                            </span>
                        </li>
                        <li
                            class="dropdown-item d-flex cursor-pointer align-items-center"
                            @click="renameFolder({{$folder->id}})"
                        >
                            <i class="fa-solid fa-i-cursor me-2"></i>
                            <span class="flex-fill me-5">
                                {{ trans('courses.finder.menu.rename')}}
                            </span>
                        </li>
                        <li
                            wire:confirm="{{ trans('courses.finder.menu.delete.folder.confirm') }}"
                            wire:click="destroyFolder({{$folder->id}})"
                            class="dropdown-item d-flex cursor-pointer align-items-center"
                        >
                            <i class="fa-regular fa-trash-can me-2"></i>
                            <span class="flex-fill me-5">
                                {{ trans('courses.finder.menu.delete')}}
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                    @endcan
                    <li
                        class="dropdown-item d-flex cursor-pointer align-items-center @if($folder->cards->isEmpty()) disabled @endif"
                        @if ($folder->cards->isNotEmpty())
                            @click="closeAllDropDowns(); window.printable.open('{{ route('cards.print', ['course' => $folder->course->id, 'cards' => $folder->cards->pluck('id')->toArray()])}}');"
                        @endif
                    >
                        <span class="flex-fill me-5">
                            <i class="fa-solid fa-print me-2"></i>
                            {{ trans('courses.finder.menu.print')}}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <ul
        class="finder-selectable-list"
        x-show="openedFolder.includes(key)"
        x-transition
        x-init="initSortable($el)"
    >
        @foreach ($items as $item)
            @if ($item->getFinderItemType() === ('App\\Enums\\FinderItemType')::Folder)
                <x-finder.folder
                    :folder="$item"
                    :sort-column="$sortColumn"
                    :sort-direction="$sortDirection"
                    :depth="$depth + 1"
                    :locked-move="$lockedMove"
                    :filters="$filters"
                    :$filterSearchBoxes
                    :$modalCloneId
                    :$modalMoveId
                />
            @else
                <x-finder.card
                    :card="$item"
                    :depth="$depth + 1"
                    :locked-move="$lockedMove"
                    :$modalCloneId
                    :$modalMoveId
                />
            @endif
        @endforeach
    </ul>
</li>

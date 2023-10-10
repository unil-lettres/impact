@props([
    'folder',
    'filters',
    'filterSearchBoxes',
    'modalCloneId',
    'modalMoveId',
    'sortColumn' => 'position',
    'sortDirection' => 'asc',
    'depth' => 0,
    'lockedMove' => false,
])
@php(
    $rows = Helpers::getFolderContent(
        $folder->course,
        $filters,
        $filterSearchBoxes,
        $folder,
        $sortColumn,
        $sortDirection,
    )
)

<li
    class="finder-folder border-top border-secondary-subtle row-height cursor-default"
    :class="!selectedItems.includes(key) || 'folder-selected'"
    data-id="{{ $folder->id }}"
    data-type="{{ $folder->getFinderRowType() }}"
    x-data="{ key: '{{ $folder->getFinderRowType() }}-{{ $folder->id }}'}"
    :data-key="key"
    @click.stop="toggleSelect($event, $el)"
    wire:dblclick.stop="openFolder({{$folder->id}})"
    wire:key='{{ $folder->getFinderRowType() }}-{{ $folder->id }}'
    {{ $lockedMove ? 'locked-move' : '' }}
>
    <div
        class="d-flex background-hover"
        :class="!selectedItems.includes(key) || 'selected'"
    >
        <div class='flex-fill overflow-hidden text-truncate px-1'>
            <div
                class="d-inline-block cursor-pointer"
                @click.stop="toggleOpen($el, key)"
                @dblclick.stop
            >
                @for ($i = 0; $i < $depth; $i++)
                    <i class="d-inline-block width-small">&nbsp;</i>
                @endfor
                @if ($rows->count() > 0)
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
            {{ $folder->position }} - {{ $folder->title }}
        </div>
        <div class="text-secondary">
            {{ Helpers::countCardsRecursive($folder, $filters, $filterSearchBoxes, $sortColumn, $sortDirection) }}
            {{ trans('courses.finder.folder.cards_count')}}
        </div>
        <div class='column-options'>
            <div class="dropdown" @click.stop>
                <button
                    class="btn border-0"
                    :class="selectedItems.length > 1 ? 'text-secondary' : ''"
                    style="width:100%"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    @click.stop="openMenu($el)"
                    @dblclick.stop
                >
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-with-icon">
                    <li
                        class="dropdown-item d-flex cursor-pointer align-items-center"
                        wire:click="openFolder({{$folder->id}})"
                    >
                        <i class="d-inline-block text-center width-large fa-regular fa-folder-open me-2"></i>
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.menu.open')}}
                        </span>
                        <span class="text-secondary ms-3 text-lowercase fw-light">
                            {{ trans('courses.finder.menu.folder.open.help')}}
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
                        <i class="d-inline-block text-center width-large fa-solid fa-folder me-2"></i>
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.menu.folder.collapse')}}
                        </span>
                        <span class="text-secondary ms-3 text-lowercase fw-light">
                            {{ trans('courses.finder.menu.folder.expand.help')}}
                        </span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    @can('moveCardOrFolder', $folder->course)
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
                    @endcan
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
                    @can('update', $folder)
                    <li
                        class="dropdown-item d-flex cursor-pointer align-items-center"
                        @click="renameFolder($wire, {{$folder->id}})"
                    >
                        <i class="fa-solid fa-i-cursor me-2"></i>
                        <span class="flex-fill me-5">
                            {{ trans('courses.finder.menu.rename')}}
                        </span>
                    </li>
                    @endcan
                    @can('forceDelete', $folder)
                        <li
                            wire:confirm="{{ trans('courses.finder.menu.delete.folder.confirm') }}"
                            wire:click="destroyFolder({{$folder->id}})"
                            class="dropdown-item d-flex cursor-pointer align-items-center"
                        >
                            <i class="fa-regular fa-trash-can me-2"></i>
                            <spa    n class="flex-fill me-5">
                                {{ trans('courses.finder.menu.delete')}}
                            </span>
                        </li>
                    @endcan
                    <li><hr class="dropdown-divider"></li>
                    <li class="dropdown-item d-flex cursor-pointer align-items-center">
                        <span class="flex-fill me-5">
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
        @foreach ($rows as $row)
            @if ($row->getFinderRowType() === ('App\\Enums\\FinderRowType')::Folder)
                <x-finder.folder
                    :folder="$row"
                    :sortColumn="$sortColumn"
                    :sortDirection="$sortDirection"
                    :depth="$depth + 1"
                    :lockedMove="$lockedMove"
                    :filters="$filters"
                    :filterSearchBoxes="$filterSearchBoxes"
                    :modalCloneId="$modalCloneId"
                    :modalMoveId="$modalMoveId"
                />
            @else
                <x-finder.card
                    :card="$row"
                    :depth="$depth + 1"
                    :lockedMove="$lockedMove"
                    :modalCloneId="$modalCloneId"
                    :modalMoveId="$modalMoveId"
                />
            @endif
        @endforeach
    </ul>
</li>

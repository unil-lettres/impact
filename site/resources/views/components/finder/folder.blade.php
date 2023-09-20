@props([
    'folder',
    'filters',
    'sortColumn' => 'position',
    'sortDirection' => 'asc',
    'depth' => 0,
    'lockedMove' => false,
])
@php($rows = $folder->getContent($sortColumn, $sortDirection, $filters))

<li
    class="border-top border-secondary-subtle row-height cursor-default"
    data-id="{{ $folder->id }}"
    data-type="{{ $folder->getType() }}"
    x-data="{ key: '{{ $folder->getType() }}-{{ $folder->id }}'}"
    :data-key="key"
    @click.stop="toggleSelect(key)"
    wire:key='{{ $folder->getType() }}-{{ $folder->id }}'
    {{ $lockedMove ? 'locked-move' : '' }}
>
    <div class="d-flex background-hover" :class="!selectedItems.includes(key) || 'selected'">
        <div class='flex-fill column-large overflow-hidden text-truncate px-1'>
            <div class="d-inline-block" @click.stop="toggleOpen($el, key)">
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
                <i class="fa-solid fa-folder d-inline-block text-center width-large"></i>
            </div>
            {{ $folder->position }} - {{ $folder->title }}
        </div>
        <div class='column-options px-1'>
            <div class="dropdown" @click.stop>
                <button class="btn p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Another action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
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
            @if ($row->getType() === ('App\\Enums\\FinderRowType')::Folder)
                <x-finder.folder
                    :folder="$row"
                    :sortColumn="$sortColumn"
                    :sortDirection="$sortDirection"
                    :depth="$depth + 1"
                    :lockedMove="$lockedMove"
                    :filters="$filters"
                />
            @else
                <x-finder.card
                    :card="$row"
                    :depth="$depth + 1"
                    :lockedMove="$lockedMove"
                />
            @endif
        @endforeach
    </ul>
</li>

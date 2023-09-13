@props([
    'folder',
    'sortColumn' => 'position',
    'sortDirection' => 'asc',
    'depth' => 0,
    'lockedMove' => false,
])
@php($rows = $folder->getContent($sortColumn, $sortDirection))

<li
    class="border-top row-height cursor-default"
    data-id="{{ $folder->id }}"
    data-type="{{ $folder->getType() }}"
    x-data="{ key: '{{ $folder->parent_id }}-{{ $folder->getType() }}-{{ $folder->id }}' }"
    @click.stop="toggleSelect(key, {{ $folder->parent_id }});"
    wire:key='{{ $folder->getType() }}-{{ $folder->id }}'
    {{ $lockedMove ? 'locked-move' : '' }}
>
    <div
        class='column-large overflow-hidden text-truncate px-1 background-hover'
        :class="!selectedItems.includes(key) || 'selected'"
    >
        <div class="d-inline-block" x-on:click.stop="openedFolder = _.xor(openedFolder, [key])">
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
    <ul
        class="finder-selectable-list"
        x-show="openedFolder.includes(key)"
        x-transition
    >
        @foreach ($rows as $row)
            @if ($row->getType() === ('App\\Enums\\FinderRowType')::Folder)
                <x-finder.folder
                    :folder="$row"
                    :sortColumn="$sortColumn"
                    :sortDirection="$sortDirection"
                    :depth="$depth + 1"
                    :lockedMove="$lockedMove"
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

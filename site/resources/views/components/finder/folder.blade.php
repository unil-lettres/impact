@props(['folder', 'depth' => 0])
@php($rows = $folder->getContent())

<li
    class="border-top row-height"
    data-id="{{ $folder->id }}"
    data-type="{{ $folder->getType() }}"
    x-data="{ open: false, key: '{{ $folder->getType() }}-{{ $folder->id }}' }"
    @click.stop="selectedItems = _.xor(selectedItems, [key])"
    wire:key='{{ $folder->getType() }}-{{ $folder->id }}'
>
    <div
        class='column-large overflow-hidden text-truncate px-1 cursor-pointer background-hover'
        x-on:click="open = ! open"
        :class="!selectedItems.includes(key) || 'selected'"
    >
        @for ($i = 0; $i < $depth; $i++)
            <i class="d-inline-block width-small">&nbsp;</i>
        @endfor
        @if ($rows->count() > 0)
            <i
                class="fa-solid fa-caret-down d-inline-block text-center width-small transition-transform"
                :class="open || 'rotate'"
            ></i>
        @else
            <i class="d-inline-block width-small">&nbsp;</i>
        @endif
        <i
            class="fa-solid fa-folder d-inline-block text-center width-large"></i>
        {{ $folder->position }} - {{ $folder->title }}
    </div>
    <ul
        class="finder-selectable-list"
        x-show="open"
        x-transition
    >
        @foreach ($rows as $row)
            @if ($row->getType() === ('App\\Enums\\FinderRowType')::Folder)
                <x-finder.folder
                    :folder="$row"
                    :depth="$depth + 1"
                />
            @else
                <x-finder.card
                    :card="$row"
                    :depth="$depth + 1"
                />
            @endif
        @endforeach
    </ul>
</li>

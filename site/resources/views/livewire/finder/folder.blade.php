<li x-data="{ open: false }">
    <div
        class='column-large overflow-hidden text-truncate px-1 cursor-pointer'
        x-on:click="open = ! open"
    >
        @for ($i = 0; $i < $this->depth; $i++)
            <i class="d-inline-block width-small">&nbsp;</i>
        @endfor
        @if ($this->rows->count() > 0)
            <i
                class="fa-solid fa-caret-down d-inline-block text-center width-small transition-transform"
                :class="open || 'rotate'"
            ></i>
        @else
            <i class="d-inline-block width-small">&nbsp;</i>
        @endif
        <i class="fa-solid fa-folder d-inline-block text-center width-large"></i>
        {{ $this->folder->position }}
        {{ $this->folder->title }}
    </div>
    <ul
        class="finder-selectable-list"
        x-show="open"
        x-transition
    >
        @foreach ($this->rows as $row)
            @if ($row->getType() === ('App\\Enums\\FinderRowType')::Folder)
                <livewire:finder.folder
                    :folder="$row"
                    :depth="$this->depth + 1"
                />
            @else
                <livewire:finder.card
                    :card="$row"
                    :depth="$this->depth + 1"
                />
            @endif
        @endforeach
    </ul>
</li>

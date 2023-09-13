@props(['card', 'selected' => false, 'depth' => 0])

<li
    class="d-flex border-top background-hover cursor-default row-height"
    data-id="{{ $card->id }}"
    data-type="{{ $card->getType() }}"
    x-data="{ key: '{{ $card->getType() }}-{{ $card->id }}' }"
    @click.stop="selectedItems = _.xor(selectedItems, [key])"
    :class="!selectedItems.includes(key) || 'selected'"
>
    <div
        class='column-large text-truncate px-1'
        title="{{ $card->title }}"
    >
        @for ($i = 0; $i < $depth; $i++)
            <i class="d-inline-block width-small">&nbsp;</i>
        @endfor
        <i class="d-inline-block text-center width-small">&nbsp;</i>
        <i
            class="d-inline-block fa-solid fa-file-lines text-center width-large"></i>
        {{ $card->position }} - {{ $card->title }}{{ $selected ? ' - selected' : '' }}
    </div>
    <div
        class='column-small text-truncate px-1 d-none d-sm-block fw-light'
        title="{{ $card->state->name }}"
    >
        {{ $card->state->name }}
    </div>
    <div
        class='column-small text-truncate px-1 d-none d-xl-block fw-light'
        title="{{ $card->created_at->format('d/m/Y') }}"
    >
        {{ $card->created_at->format('d/m/Y') }}
    </div>
    <div
        class='column-medium text-truncate px-1 d-none d-lg-block fw-light'
        title="{{ $card->editors()->pluck('name')->join(', ') }}"
    >
        {{ $card->editors()->pluck('name')->join(', ') }}
    </div>
    <div
        class='column-medium text-truncate px-1 d-none d-lg-block fw-light'
        title="{{ $card->tags->pluck('name')->join(', ') }}"
    >
        {{ $card->tags->pluck('name')->join(', ') }}
    </div>
</li>

@props(['card', 'selected' => false, 'lockedMove' => false, 'depth' => 0])

<li
    class="card-row d-flex border-top border-secondary-subtle background-hover cursor-default row-height"
    data-id="{{ $card->id }}"
    data-type="{{ $card->getType() }}"
    x-data="{ key: '{{ $card->getType() }}-{{ $card->id }}' }"
    :data-key="key"
    @click.stop="toggleSelect(key)"
    :class="!selectedItems.includes(key) || 'selected'"
    wire:key='{{ $card->getType() }}-{{ $card->id }}'
    {{ $lockedMove ? 'locked-move' : '' }}
>
    <div
        class='column-large text-truncate px-1 position-relative'
        title="{{ $card->title }}"
    >
        @for ($i = 0; $i < $depth; $i++)
            <i class="d-inline-block width-small">&nbsp;</i>
        @endfor
        <i class="d-inline-block text-center width-small">&nbsp;</i>
        <i
            class="d-inline-block fa-solid fa-file-lines text-center width-large"></i>
        {{ $card->position }} - {{ $card->title }}{{ $selected ? ' - selected' : '' }}
        <a
            href="{{ route('cards.show', $card->id) }}"
            class="text-decoration-none d-none cursor-pointer bg-white border text-secondary position-absolute translate-middle-y top-50 end-0 shadow-sm text-uppercase fs-7 lh-base py-1 px-2 rounded me-3"
        >
            <i class="fa-solid fa-square-arrow-up-right"></i>
            {{ trans('cards.open') }}
        </a>
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
        title="{{ $card->editors_list }}"
    >
        {{ $card->editors_list }}
    </div>
    <div
        class='column-medium text-truncate px-1 d-none d-lg-block fw-light'
        title="{{ $card->tags_list }}"
    >
        {{ $card->tags_list }}
    </div>
</li>

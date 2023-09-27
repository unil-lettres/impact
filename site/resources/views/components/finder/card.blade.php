@props(['card', 'modalId', 'selected' => false, 'lockedMove' => false, 'depth' => 0])

<li
    class="finder-card d-flex border-top border-secondary-subtle background-hover cursor-default row-height"
    data-id="{{ $card->id }}"
    data-type="{{ $card->getType() }}"
    x-data="{ key: '{{ $card->getType() }}-{{ $card->id }}' }"
    :data-key="key"
    @click.stop="toggleSelect($el, key)"
    @dblclick.stop="window.location = '{{ route('cards.show', $card->id) }}'"
    :class="!selectedItems.includes(key) || 'selected'"
    wire:key='{{ $card->getType() }}-{{ $card->id }}'
    {{ $lockedMove ? 'locked-move' : '' }}
>
    <div
        class='flex-fill text-truncate px-1 position-relative'
        title="{{ $card->title }}"
    >
        @for ($i = 0; $i < $depth; $i++)
            <i class="d-inline-block width-small">&nbsp;</i>
        @endfor
        <i class="d-inline-block text-center width-small">&nbsp;</i>
        <a href="{{ route('cards.show', $card->id) }}" class="text-decoration-none text-primary">
            <i class="d-inline-block fa-solid fa-file-lines text-center width-large"></i>
        </a>
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
            <ul class="dropdown-menu">
                <li class="dropdown-item d-flex cursor-pointer align-items-center"
                    @click="window.location = '{{ route('cards.show', $card->id) }}'"
                >
                    <i class="fa-solid fa-square-arrow-up-right me-2"></i>
                    <span class="flex-fill me-5">{{ trans('courses.finder.menu.open')}}</span>
                    <span class="text-secondary ms-3 text-lowercase fs-7 fw-light">{{ trans('courses.finder.menu.open.help')}}</span>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-item d-flex cursor-pointer align-items-center">
                    <span class="flex-fill me-5">{{ trans('courses.finder.menu.move')}}</span>
                </li>
                <li
                    class="dropdown-item d-flex cursor-pointer align-items-center"
                    wire:click="cloneCard({{$card->id}})"
                >
                    <i class="fa-regular fa-copy me-2"></i>
                    <span class="flex-fill me-5">{{ trans('courses.finder.menu.copy')}}</span>
                </li>
                <li
                    class="dropdown-item d-flex cursor-pointer align-items-center"
                    data-bs-toggle="modal"
                    data-bs-target="#{{$modalId}}"
                    :data-bs-keys="key"
                >
                    <span class="flex-fill me-5">{{ trans('courses.finder.menu.copy_in')}}</span>
                </li>
                @can('forceDelete', $card)
                    <li wire:confirm="{{ trans('courses.finder.menu.delete.card.confirm') }}" wire:click="destroyCard({{$card->id}})"
                        class="dropdown-item d-flex cursor-pointer align-items-center"
                    >
                        <i class="fa-regular fa-trash-can me-2"></i>
                        <span class="flex-fill me-5">{{ trans('courses.finder.menu.delete')}}</span>
                    </li>
                @endcan
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-item d-flex cursor-pointer align-items-center">
                    <span class="flex-fill me-5">{{ trans('courses.finder.menu.print')}}</span>
                </li>
                <li class="dropdown-item d-flex cursor-pointer align-items-center">
                    <span class="flex-fill me-5">{{ trans('courses.finder.menu.mail')}}</span>
                </li>
            </ul>
        </div>
    </div>
</li>

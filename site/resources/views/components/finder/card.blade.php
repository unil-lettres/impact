@props([
    'card',
    'modalCloneId',
    'modalMoveId',
    'selected' => false,
    'depth' => 0,
])

@php
    // Each of these is read several times below, and resolving one walks the
    // state permissions of every box of the card, so they are resolved once.
    $canView = auth()->user()->can('view', $card);
    $canUpdate = auth()->user()->can('update', $card);
    $canManage = auth()->user()->can('manage', $card);
@endphp

<li
    class="@unless($canView) disabled @endunless finder-card d-flex border-top border-secondary-subtle background-hover cursor-default row-height"
    data-id="{{ $card->id }}"
    data-type="{{ $card->getFinderItemType() }}"
    dusk="finder-card-{{ $card->id }}"
    x-data="{ key: '{{ $card->getFinderItemType() }}-{{ $card->id }}', mouseover: false }"
    :data-key="key"
    @mouseover.stop="mouseover = true"
    @mouseout.stop="mouseover = false"
    @click.stop="toggleSelect($event, $el)"
    :class="!selectedItems.includes(key) || 'selected'"
    wire:key='{{ $card->getFinderItemType() }}-{{ $card->id }}'
    wire:sort:item="{{ $card->getFinderItemType() }}-{{ $card->id }}"
>
    <div
        class='flex-fill text-truncate px-1 position-relative'
        title="{{ $card->title }}"
    >
        <input
            class="opacity-0"
            :class="(!selectedItems.includes(key) && !mouseover) || 'opacity-100'"
            type="checkbox"
            :checked="selectedItems.includes(key)"
        />
        @for ($i = 0; $i < $depth; $i++)
            <i class="d-inline-block width-small">&nbsp;</i>
        @endfor
        <i class="d-inline-block text-center width-small">&nbsp;</i>
        @if($canView)
            <a
                href="{{ route('cards.show', $card->id) }}"
                class="text-decoration-none @if($canUpdate) text-primary @else text-black @endif"
                @click.stop
            >
                <i class="d-inline-block fas fa-file-lines text-center width-large"></i>
            </a>
            <a
                href="{{ route('cards.show', $card->id) }}"
                class="legacy text-black"
                @click.stop
            >
                {{ $card->title }}
            </a>
        @else
            <i class="d-inline-block fas fa-file-lines text-center width-large"></i>
            <span class="text-secondary">{{ $card->title }}</span>
        @endif
    </div>
    <div
        class='column-small text-truncate px-1 d-none d-sm-block fw-light'
        title="{{ $card->state?->name }}"
    >
        {{ $card->state?->name }}
    </div>
    <div
        class='column-small text-truncate px-1 d-none d-xl-block fw-light'
        title="{{ $card->created_at->format('d/m/Y') }}"
    >
        {{ $card->created_at->format('d/m/Y') }}
    </div>
    <div
        class='column-medium text-truncate px-1 d-none d-lg-block fw-light'
        title="{{ $card->holders_list }}"
    >
        @foreach ($card->holders() as $holder)
            <span class="{{ $holder->isValid() ? '' : 'expired' }}">{{ $holder->name }}</span>{{ !$loop->last ? ', ' : '' }}
        @endforeach
    </div>
    <div
        class='column-medium text-truncate px-1 d-none d-lg-block fw-light'
        title="{{ $card->tags_list }}"
    >
        {{ $card->tags_list }}
    </div>
    <div class='column-options'>
        @if($canView || $canManage)
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
                    <i class="fas fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-with-icon">
                    @if($canView)
                        <li class="dropdown-item d-flex cursor-pointer align-items-center"
                            @click="window.location = '{{ route('cards.show', $card->id) }}'"
                        >
                            <i class="fas fa-square-arrow-up-right me-2"></i>
                            <span class="flex-fill me-5">
                                {{ trans('courses.finder.menu.open')}}
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                    @endif
                    @if($canManage)
                        <li
                            class="dropdown-item d-flex cursor-pointer align-items-center"
                            data-bs-toggle="modal"
                            data-bs-target="#{{$modalMoveId}}"
                            :data-bs-keys="[key]"
                        >
                            <i class="fas fa-arrow-right-to-bracket me-2"></i>
                            <span class="flex-fill me-5">
                                {{ trans('courses.finder.move_in')}}
                            </span>
                        </li>
                        <li
                            class="dropdown-item d-flex cursor-pointer align-items-center"
                            wire:click="cloneCard({{$card->id}})"
                        >
                            <i class="fas fa-copy me-2"></i>
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
                            <i class="fas fa-file-import me-2"></i>
                            <span class="flex-fill me-5">
                                {{ trans('courses.finder.clone_in')}}
                            </span>
                        </li>
                        @if($canUpdate)
                            <li
                                class="dropdown-item d-flex cursor-pointer align-items-center"
                                data-bs-toggle="modal"
                                data-bs-target="#modalUpdateState"
                                data-bs-cards="{{ $card->id }}"
                                data-bs-state="{{ $card->state->id }}"
                            >
                                <i class="fas fa-timeline me-2"></i>
                                <span class="flex-fill me-5">
                                    {{ trans('courses.finder.dialog.update_state.title')}}
                                </span>
                            </li>
                            <li
                                class="dropdown-item d-flex cursor-pointer align-items-center"
                                @click="renameCard({{$card->id}}, '{{addslashes($card->title)}}')"
                            >
                                <i class="fas fa-i-cursor me-2"></i>
                                <span class="flex-fill me-5">
                                    {{ trans('courses.finder.menu.rename')}}
                                </span>
                            </li>
                        @endif
                        <li
                            wire:confirm="{{ trans('courses.finder.menu.delete.card.confirm') }}"
                            wire:click="destroyCard({{$card->id}})"
                            class="dropdown-item d-flex cursor-pointer align-items-center"
                        >
                            <i class="fas fa-trash-can me-2"></i>
                            <span class="flex-fill me-5">
                                {{ trans('courses.finder.menu.delete')}}
                            </span>
                        </li>
                        @if($canView)<li><hr class="dropdown-divider"></li> @endif
                    @endif
                    @if($canView)
                        <li
                            class="dropdown-item d-flex cursor-pointer align-items-center"
                            @click="closeAllDropDowns(); window.printable.open('{{ route('cards.print', ['cards' => [$card->id]])}}');"
                        >
                            <i class="fas fa-print me-2"></i>
                            <span class="flex-fill me-5">
                                {{ trans('courses.finder.menu.print')}}
                            </span>
                        </li>
                    @endif
                </ul>
            </div>
        @endif
    </div>
</li>

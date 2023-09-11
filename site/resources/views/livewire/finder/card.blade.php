<li class="d-flex">
    <div
        class='column-large text-truncate px-1'
        title="{{ $this->card->title }}"
    >
        @for ($i = 0; $i < $this->depth; $i++)
            <i class="d-inline-block width-small">&nbsp;</i>
        @endfor
        <i class="d-inline-block text-center width-small">&nbsp;</i>
        <i
            class="d-inline-block fa-solid fa-file-lines text-center width-large"></i>
        {{ $this->card->position }} - {{ $this->card->title }}
    </div>
    <div
        class='column-medium text-truncate px-1 d-none d-lg-block'
        title="{{ $this->card->editors()->pluck('name')->join(', ') }}"
    >
        {{ $this->card->editors()->pluck('name')->join(', ') }}
    </div>
    <div
        class='column-small text-truncate px-1 d-none d-sm-block'
        title="{{ $this->card->state->name }}"
    >
        {{ $this->card->state->name }}
    </div>
    <div
        class='column-small text-truncate px-1 d-none d-lg-block'
        title="{{ $this->card->created_at->format('d/m/Y') }}"
    >
        {{ $this->card->created_at->format('d/m/Y') }}
    </div>
    <div
        class='column-small text-truncate px-1 d-none d-lg-block d-lg-none'
        title="{{ $this->card->created_at->format('d/m/y') }}"
    >
        {{ $this->card->created_at->format('d/m/y') }}
    </div>
    <div
        class='column-medium text-truncate px-1 d-none d-lg-block'
        title="{{ $this->card->tags->pluck('name')->join(', ') }}"
    >
        {{ $this->card->tags->pluck('name')->join(', ') }}
    </div>
</li>

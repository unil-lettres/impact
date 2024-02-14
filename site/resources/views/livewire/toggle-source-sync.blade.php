<button
    class="btn @if ($card->options['box2']['sync']) btn-primary @else btn-secondary @endif"
    title="{{ trans('cards.sync_with_source') }}"
    wire:click="toggle"
>
    <div wire:loading.remove>
        <i class="fa-solid fa-arrow-right-arrow-left"></i>
    </div>

    <div wire:loading class="spinner-border text-light spinner-border-sm fs-5" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</button>

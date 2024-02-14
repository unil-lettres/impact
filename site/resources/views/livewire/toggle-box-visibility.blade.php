<div>
    <button
        class="btn @if ($card->options[$box]['hidden']) btn-secondary @else btn-primary @endif"
        title="{{ trans('cards.hide_boxe.tooltip') }}"
        wire:click="toggle">
            <div wire:loading.remove >
                <i class="far fa-eye"></i>
            </div>

            <div wire:loading class="spinner-border text-light spinner-border-sm fs-5" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
    </button>
</div>

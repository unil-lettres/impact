<div>
    @can('update', $card)
        @if (!$editing)
            <button
                class="btn p-0 fs-2 d-flex align-items-center gap-2 show-icon-on-hover"
                type="button"
                wire:click="edit()"
            >
                {{ $card->title }}
                <i wire:loading.remove class="fs-6 fa-solid fa-pen"></i>
                <div wire:loading class="spinner-border text-primary spinner-border-sm fs-5" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </button>
        @else
            <form wire:submit="save" class="row row-cols-2 g-3 align-items-start">
                <div class="col">
                    <div class="d-flex flex-column gap-2">
                        <label class="visually-hidden" for="inlineFormInputGroupUsername">{{ trans('cards.rename.label') }}</label>
                        <input x-init="$el.select()" autocomplete="off" type="text" class="form-control" id="inlineFormInputGroupUsername" wire:model="title" placeholder="{{ $card->title }}">
                        @error('title') <div class="text-danger fs-6"> {{ $message }} </div> @enderror
                    </div>
                </div>

                <div class="col">
                    <button type="submit" class="btn btn-primary">{{ trans('cards.rename.submit') }}</button>
                    <button type="button" class="btn btn-secondary" wire:click="$set('editing', false)">{{ trans('cards.rename.cancel') }}</button>
                    <div wire:loading class="spinner-border text-primary spinner-border-sm fs-5" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </form>
        @endif
    @else
        <div class="fs-2">{{ $card->title }}</div>
    @endcan
</div>


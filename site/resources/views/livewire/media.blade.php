<div class="media" {{ $card->boxIsEditable($reference) ? 'wire:poll' : '' }}>
    @if(Helpers::showMediaStatus($card) || $mediaStatusIsActive)
        <livewire:media-status :card="$card" key="{{ $this->mediaStatusKey() }}"/>
    @else
        @if(Helpers::cardHasExternalLink($card))
            <div id="rct-player" wire:ignore
                 data='{{ json_encode(['locale' => Helpers::currentLocal(), 'card' => $card, 'url' => Helpers::getCardExternalLink($card), 'isLocal' => false]) }}'
            ></div>
        @elseif(Helpers::isFileStatus($card->file, \App\Enums\FileStatus::Ready))
            <div id="rct-player" wire:ignore
                 data='{{ json_encode(['locale' => Helpers::currentLocal(), 'card' => $card, 'url' => Helpers::fileUrl($card->file->filename), 'isLocal' => true]) }}'
            ></div>
        @endif
    @endif
</div>

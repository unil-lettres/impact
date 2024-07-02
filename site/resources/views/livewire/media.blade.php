<div class="media" {{ $card->boxIsEditable($reference) ? 'wire:poll' : '' }}>
    @if($this->showMediaStatus($card))
        <livewire:media-status :card="$card" key="{{ $this->mediaStatusKey() }}"/>
    @else
        @if(Helpers::cardHasExternalLink($card))
            @if($this->youtubeVideoParams)
                <div class="youtube-container">
                    <iframe
                        width="100%"
                        src="//www.youtube.com/embed/{{$this->youtubeVideoParams['id']}}?start={{$this->youtubeVideoParams['start']}}"
                        frameborder="0"
                    ></iframe>
                </div>
            @else
                <div id="rct-player" wire:ignore
                    data='{{ json_encode(['locale' => Helpers::currentLocal(), 'card' => $card, 'url' => Helpers::getCardExternalLink($card), 'isLocal' => false]) }}'
                ></div>
            @endif
        @elseif(Helpers::isFileStatus($card->file, \App\Enums\FileStatus::Ready))
            <div id="rct-player" wire:ignore
                 data='{{ json_encode(['locale' => Helpers::currentLocal(), 'card' => $card, 'url' => Helpers::fileUrl($card->file->filename), 'isLocal' => true]) }}'
            ></div>
        @endif
    @endif
</div>

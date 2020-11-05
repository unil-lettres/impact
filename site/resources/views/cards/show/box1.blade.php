<div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hidden' : '' }}">
    <div class="card-header">
        <span class="font-weight-bolder">1. {{ trans('cards.source') }}</span>

        @can('upload', [\App\File::class, $course, $card])
            <input id="card_id" name="card_id" type="hidden" value="{{ $card->id }}">
            <input id="course_id" name="course_id" type="hidden" value="{{ $course->id }}">

            <div id="rct-uploader" class="float-right"
                 data='{{ json_encode(['locale' => Helpers::currentLocal(), 'modal' => true, 'label' => 'Envoyer']) }}'
            ></div>
        @endcan
    </div>
    <div class="card-body p-0">
        @if(Helpers::hasSource($card))
            @if(Helpers::hasExternalLink($card))
                <div id="rct-player"
                     data='{{ json_encode(['locale' => Helpers::currentLocal(), 'card' => $card, 'url' => Helpers::getExternalLink($card), 'isLocal' => false]) }}'
                ></div>
            @elseif($card->file)
                @if(Helpers::isFileReady($card->file))
                    <div id="rct-player"
                         data='{{ json_encode(['locale' => Helpers::currentLocal(), 'card' => $card, 'url' => Helpers::fileUrl($card->file->filename), 'isLocal' => true]) }}'
                    ></div>
                @elseif(Helpers::isFileFailed($card->file))
                    <p class="text-danger text-center p-3">
                        {{ trans('messages.card.media.failed') }}
                    </p>
                @else
                    <p class="text-primary text-center p-3">
                        {{ trans('messages.card.media.processing') }}
                    </p>
                @endif
            @endif
        @else
            <p class="text-secondary text-center p-3">
                {{ trans('messages.card.media.not.selected') }}
            </p>
        @endif
    </div>
</div>

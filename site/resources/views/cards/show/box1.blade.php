@if($card->boxIsVisible($reference))
    <div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hidden' : '' }}">
        <div class="card-header">
            <span class="fw-bolder">1. {{ trans('cards.source') }}</span>

            @if($card->boxIsEditable($reference))
                @can('upload', [\App\File::class, $course, $card])
                    <div id="rct-files" class="float-end"
                         data='{{ json_encode(['locale' => Helpers::currentLocal(), 'label' => trans('files.upload'), 'course_id' => $course->id, 'card_id' => $card->id, 'note' => trans('messages.file.reload')]) }}'
                    ></div>
                @endcan
            @endif
        </div>
        <div class="card-body p-0">
            @if(Helpers::hasSource($card))
                @if(Helpers::hasExternalLink($card))
                    <div id="rct-player"
                         data='{{ json_encode(['locale' => Helpers::currentLocal(), 'card' => $card, 'url' => Helpers::getExternalLink($card), 'isLocal' => false]) }}'
                    ></div>
                @elseif($card->file)
                    @if(Helpers::isFileStatus($card->file, \App\Enums\FileStatus::Ready))
                        <div id="rct-player"
                             data='{{ json_encode(['locale' => Helpers::currentLocal(), 'card' => $card, 'url' => Helpers::fileUrl($card->file->filename), 'isLocal' => true]) }}'
                        ></div>
                    @elseif(Helpers::isFileStatus($card->file, \App\Enums\FileStatus::Failed))
                        <p class="text-danger text-center fs-5 lh-sm p-3">
                            {{ trans('messages.card.media.failed') }}
                        </p>
                    @else
                        <p class="text-secondary text-center fs-5 lh-sm p-3">
                            {{ trans('messages.card.media.processing') }}
                        </p>
                    @endif
                @endif
            @else
                <p class="text-secondary text-center p-4">
                    {{ trans('messages.card.media.not.selected') }}
                </p>
            @endif
        </div>
    </div>
@endif

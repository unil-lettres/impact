@if($card->boxIsVisible($reference))
    <div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hidden' : '' }}">
        <div class="card-header">
            <span class="fw-bolder">2. {{ trans('cards.transcription') }}</span>
            <span class="d-none" id="edit-failed-{{ $reference }}">[ {{ trans('messages.card.editor.failed') }} ]</span>

            @if($card->boxIsEditable($reference))
                <button class="btn btn-primary float-end"
                        id="edit-{{ $reference }}">
                    {{ trans('cards.edit') }}
                </button>
                <button class="btn btn-secondary d-none float-end me-2"
                        id="cancel-{{ $reference }}">
                    {{ trans('cards.cancel') }}
                </button>

                @if($card->course->transcription === \App\Enums\TranscriptionType::Icor)
                    <button class="btn btn-danger d-none float-end me-2"
                            id="clear-{{ $reference }}">
                        {{ trans('cards.clear_transcription') }}
                    </button>
                    <button class="btn btn-primary float-end me-2"
                            id="export-{{ $reference }}"
                            format="docx"
                            data-bs-toggle="tooltip"
                            data-placement="top"
                            title="{{ trans('cards.export') }}">
                        <i class="far fa-arrow-alt-circle-down"></i>
                    </button>
                @endif
            @endif
        </div>
        <div id="transcription-viewer" class="card-body">
            <p id="empty-{{ $reference }}"
               class="text-secondary text-center d-none p-3">
                {{ trans('messages.card.no.transcription') }}
            </p>
            @if($card->course->transcription === \App\Enums\TranscriptionType::Icor)
                <div id="rct-transcription"
                     data='{{ json_encode(['card' => $card, 'editLabel' => trans('cards.edit'), 'saveLabel' => trans('cards.save'), 'deleteLineActionLabel' => trans('cards.delete_line_action'), 'toggleNumberActionLabel' => trans('cards.toggle_number_action')]) }}'
                     reference='{{ $reference }}'
                ></div>
            @elseif($card->course->transcription === \App\Enums\TranscriptionType::Text)
                <div id="rct-editor-{{ $reference }}"
                     data='{{ json_encode(['cardId' => $card->id, 'content' => $card->$reference['text'], 'locale' => Helpers::currentLocal(), 'editLabel' => trans('cards.edit'), 'saveLabel' => trans('cards.save')]) }}'
                     reference='{{ $reference }}'
                ></div>
            @endif
        </div>
    </div>
@endif

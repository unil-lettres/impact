@if($card->boxIsVisible($reference))
    <div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hidden' : '' }}">
        <div class="card-header">
            <div class="d-flex gap-2 align-items-center">
                <span class="fw-bolder me-auto">2. {{ trans('cards.transcription') }}</span>
                <span class="d-none" id="edit-failed-{{ $reference }}">[ {{ trans('messages.card.editor.failed') }} ]</span>

                @can('update', $card)
                    <div id="sync-{{ $reference }}"><livewire:toggle-source-sync :card="$card" /></div>
                @endcan

                @if($card->boxIsEditable($reference))

                    @if($card->course->transcription === \App\Enums\TranscriptionType::Icor)
                        <button class="btn btn-primary"
                                id="export-{{ $reference }}"
                                format="docx"
                                data-bs-toggle="tooltip"
                                data-placement="top"
                                title="{{ trans('cards.export') }}">
                            <i class="far fa-arrow-alt-circle-down"></i>
                        </button>

                        <button class="btn btn-danger d-none"
                                id="clear-{{ $reference }}">
                            {{ trans('cards.clear_transcription') }}
                        </button>
                    @endif

                    <button class="btn btn-secondary d-none"
                            id="cancel-{{ $reference }}">
                        {{ trans('cards.cancel') }}
                    </button>

                    <button class="btn btn-primary"
                            id="edit-{{ $reference }}">
                        {{ trans('cards.edit') }}
                    </button>
                @endif
            </div>
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
                     data='{{ json_encode(['cardId' => $card->id, 'content' => $card->$reference['text'], 'locale' => Helpers::currentLocal(), 'editLabel' => trans('cards.edit'), 'saveLabel' => trans('cards.save'), 'placeholder' => trans('cards.add_content')]) }}'
                     reference='{{ $reference }}'
                ></div>
            @endif
        </div>
    </div>
@endif

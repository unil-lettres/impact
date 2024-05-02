@if($card->boxIsVisible($reference))
    <div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hide-on-read-only' : '' }}">
        <div class="card-header">
            <div class="d-flex align-items-center">
                <span class="fw-bolder">2. {{ trans('cards.transcription') }}</span>
                <div class="me-auto mx-2 text-danger">
                    <livewire:transcription-counter :card="$card" />
                </div>
                <div class="d-flex">
                    <span class="d-none" id="edit-failed-{{ $reference }}">[ {{ trans('messages.card.editor.failed') }} ]</span>

                    @if($card->boxIsEditable($reference))
                        @can('parameters', $card)
                            <div class="hide-on-read-only">
                                <div id="hide-{{ $reference }}"
                                     class="ms-2">
                                    <livewire:toggle-box-visibility :card="$card" box="box2" />
                                </div>
                            </div>
                        @endcan

                        @if($card->course->transcription === \App\Enums\TranscriptionType::Icor)
                            @can('update', $card)
                                <div class="hide-on-read-only">
                                    <div id="sync-{{ $reference }}"
                                         class="ms-2">
                                        <livewire:toggle-source-sync :card="$card" />
                                    </div>
                                </div>
                            @endcan

                            <div class="hide-on-read-only">
                                <button class="btn btn-primary ms-2"
                                        id="import-{{ $reference }}"
                                        format="icor"
                                        data-bs-toggle="modal"
                                        data-bs-target="#importModal"
                                        title="{{ trans('cards.import') }}">
                                    <i class="far fa-arrow-alt-circle-up"></i>
                                </button>
                            </div>

                            <div class="hide-on-read-only">
                                <button class="btn btn-primary ms-2"
                                        id="export-{{ $reference }}"
                                        format="docx"
                                        title="{{ trans('cards.export') }}">
                                    <i class="far fa-arrow-alt-circle-down"></i>
                                </button>
                            </div>

                            <div class="hide-on-read-only">
                                <button class="btn btn-danger d-none ms-2"
                                        id="clear-{{ $reference }}">
                                    {{ trans('cards.clear_transcription') }}
                                </button>
                            </div>
                        @endif

                        <div class="hide-on-read-only">
                            <button class="btn btn-secondary d-none ms-2"
                                    id="cancel-{{ $reference }}">
                                {{ trans('cards.cancel') }}
                            </button>
                        </div>

                        <div class="hide-on-read-only">
                            <button class="btn btn-primary ms-2"
                                    id="edit-{{ $reference }}">
                                {{ trans('cards.edit') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div id="transcription-viewer" class="card-body">
            <p id="empty-{{ $reference }}"
               class="text-secondary text-center d-none p-3">
                {{ trans('messages.card.no.transcription') }}
            </p>
            @if($card->course->transcription === \App\Enums\TranscriptionType::Icor)
                <div id="rct-transcription"
                     data='{{ json_encode(['card' => $card, 'editLabel' => trans('cards.edit'), 'saveLabel' => trans('cards.save'), 'cancelLabel' => trans('cards.cancel'), 'deleteLineActionLabel' => trans('cards.delete_line_action'), 'toggleNumberActionLabel' => trans('cards.toggle_number_action'), 'importModalTitleLabel' => trans('cards.import'), 'importModalHelpLabel' => trans('cards.import.help')]) }}'
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

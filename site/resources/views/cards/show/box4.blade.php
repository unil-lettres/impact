@if($card->boxIsVisible($reference))
    <div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hidden' : '' }}">
        <div class="card-header">
            <div class="d-flex gap-2 align-items-center">
                <span class="fw-bolder me-auto">4. {{ $card->options[$reference]['title'] }}</span>
                <span class="d-none" id="edit-failed-{{ $reference }}">[ {{ trans('messages.card.editor.failed') }} ]</span>

                @if($card->boxIsEditable($reference))
                    @can('parameters', $card)
                        <div id="hide-{{ $reference }}">
                            <livewire:toggle-box-visibility :card="$card" box="box4" />
                        </div>
                    @endcan

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
        <div class="card-body">
            <p id="empty-{{ $reference }}"
               class="text-secondary text-center d-none p-3">
                {{ trans('messages.card.empty') }}
            </p>
            <div id="rct-editor-{{ $reference }}"
                 data='{{ json_encode(['cardId' => $card->id, 'content' => $card->$reference, 'locale' => Helpers::currentLocal(), 'editLabel' => trans('cards.edit'), 'saveLabel' => trans('cards.save'), 'placeholder' => trans('cards.add_content')]) }}'
                 reference='{{ $reference }}'
            ></div>
        </div>
    </div>
@endif

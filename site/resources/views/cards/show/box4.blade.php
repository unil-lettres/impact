@if($card->boxIsVisible($reference))
    <div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hidden' : '' }}">
        <div class="card-header">
            <span class="fw-bolder">4. {{ $card->options[$reference]['title'] }}</span>
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
            @endif
        </div>
        <div class="card-body">
            <p id="empty-{{ $reference }}"
               class="text-secondary text-center d-none p-3">
                {{ trans('messages.card.empty') }}
            </p>
            <div id="rct-editor-{{ $reference }}"
                 data='{{ json_encode(['cardId' => $card->id, 'content' => $card->$reference, 'locale' => Helpers::currentLocal(), 'editLabel' => trans('cards.edit'), 'saveLabel' => trans('cards.save')]) }}'
                 reference='{{ $reference }}'
            ></div>
        </div>
    </div>
@endif

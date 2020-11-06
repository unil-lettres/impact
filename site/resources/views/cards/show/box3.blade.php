<div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hidden' : '' }}">
    <div class="card-header">
        <span class="font-weight-bolder">3. {{ $card->options[$reference]['title'] }}</span>
        <span class="d-none" id="edit-failed-{{ $reference }}">[ {{ trans('messages.card.editor.failed') }} ]</span>
        @can('editor', $card)
            <button class="btn btn-primary float-right"
                    id="edit-{{ $reference }}">
                {{ trans('cards.edit') }}
            </button>
            <button class="btn btn-secondary d-none float-right mr-2"
                    id="cancel-{{ $reference }}">
                {{ trans('cards.cancel') }}
            </button>
        @endcan
    </div>
    <div class="card-body">
        <p id="empty-{{ $reference }}"
           class="text-secondary text-center d-none p-3">
            {{ trans('messages.card.empty') }}
        </p>
        <div id="rct-editor-{{ $reference }}"
             data='{{ json_encode(['card' => $card, 'locale' => Helpers::currentLocal(), 'editLabel' => trans('cards.edit'), 'saveLabel' => trans('cards.save')]) }}'
             reference='{{ $reference }}'
        ></div>
    </div>
</div>

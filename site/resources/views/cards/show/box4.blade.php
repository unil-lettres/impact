<div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hidden' : '' }}">
    <div class="card-header">
        <span class="font-weight-bolder">4. {{ $card->options[$reference]['title'] }}</span>
        <span class="d-none" id="edit-failed-{{ $reference }}">[ {{ trans('messages.card.editor.failed') }} ]</span>
        @can('editor', $card)
            <button class="btn btn-primary float-right"
                    id="edit-{{ $reference }}">
                {{ trans('cards.edit') }}
            </button>
        @endcan
    </div>
    <div class="card-body">
        <div id="rct-editor-{{ $reference }}"
             data='{{ json_encode(['card' => $card, 'locale' => Helpers::currentLocal(), 'editLabel' => trans('cards.edit'), 'saveLabel' => trans('cards.save')]) }}'
             reference='{{ $reference }}'
        ></div>
    </div>
</div>

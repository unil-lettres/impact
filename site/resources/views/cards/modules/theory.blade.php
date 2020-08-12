<div class="card">
    <div class="card-header">
        <span class="font-weight-bolder">3. {{ trans('cards.theory') }}</span>
        <span class="d-none" id="edit-failed-box3">[ {{ trans('messages.card.editor.failed') }} ]</span>
        <button class="btn btn-primary float-right"
                id="edit-box3">
            {{ trans('cards.edit') }}
        </button>
    </div>
    <div class="card-body">
        <div id="rct-editor-box3"
             data='{{ json_encode(['card' => $card, 'locale' => Helpers::currentLocal(), 'editLabel' => trans('cards.edit'), 'saveLabel' => trans('cards.save')]) }}'
             reference='box3'
        ></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="font-weight-bolder">4. {{ trans('cards.exemplification') }}</span>
        <span class="d-none" id="edit-failed-box4">[ {{ trans('messages.card.editor.failed') }} ]</span>
        <button class="btn btn-primary float-right"
                id="edit-box4">
            {{ trans('cards.edit') }}
        </button>
    </div>
    <div class="card-body">
        <div id="rct-editor-box4"
             data='{{ json_encode(['card' => $card, 'locale' => Helpers::currentLocal(), 'editLabel' => trans('cards.edit'), 'saveLabel' => trans('cards.save')]) }}'
             reference='box4'
        ></div>
    </div>
</div>

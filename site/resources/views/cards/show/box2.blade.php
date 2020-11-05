<div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hidden' : '' }}">
    <div class="card-header">
        <span class="font-weight-bolder">2. {{ trans('cards.transcription') }}</span>
        <span class="d-none" id="edit-failed-{{ $reference }}">[ {{ trans('messages.card.editor.failed') }} ]</span>
        @can('editor', $card)
            <button class="btn btn-primary float-right"
                    id="edit-{{ $reference }}">
                {{ trans('cards.edit') }}
            </button>
            <button class="btn btn-danger d-none float-right mr-2"
                    id="clear-{{ $reference }}">
                {{ trans('cards.clear_transcription') }}
            </button>
            <button class="btn btn-secondary d-none float-right mr-2"
                    id="cancel-{{ $reference }}">
                {{ trans('cards.cancel') }}
            </button>
            <button class="btn btn-primary float-right mr-2"
                    id="export-{{ $reference }}"
                    format="docx"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="{{ trans('cards.export') }}">
                <i class="far fa-arrow-alt-circle-down"></i>
            </button>
        @endcan
    </div>
    <div id="transcription-viewer" class="card-body">
        <div id="rct-transcription"
             data='{{ json_encode(['card' => $card, 'editLabel' => trans('cards.edit'), 'saveLabel' => trans('cards.save'), 'deleteLineActionLabel' => trans('cards.delete_line_action'), 'toggleNumberActionLabel' => trans('cards.toggle_number_action')]) }}'
             reference='{{ $reference }}'
        ></div>
    </div>
</div>

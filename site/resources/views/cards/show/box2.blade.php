<div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hidden' : '' }}">
    <div class="card-header">
        <span class="font-weight-bolder">2. {{ trans('cards.transcription') }}</span>
        @can('editor', $card)
            <button class="btn btn-primary float-right"
                    id="edit-{{ $reference }}">
                {{ trans('cards.edit') }}
            </button>
        @endcan
    </div>
    <div class="card-body">
        <div id="rct-transcription"
             data='{{ json_encode(['card' => $card, 'locale' => Helpers::currentLocal(), 'editLabel' => trans('cards.edit'), 'saveLabel' => trans('cards.save')]) }}'
             reference='{{ $reference }}'
        ></div>
    </div>
</div>

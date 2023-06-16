@can('parameters', $card)
    <div class="card">
        <div class="card-header">
            {{ trans('cards.editors') }}
        </div>
        <div class="card-body">
            <p>{{ trans('cards.choose_editors') }}</p>

            @if ($students->isNotEmpty())
                <div class="col-12 mb-3">
                    <div id="rct-multi-editor-select"
                         data='{{ json_encode(['record' => $card, 'options' => $students, 'defaults' => $editors]) }}'
                    ></div>
                </div>
            @else
                <p class="text-secondary">
                    {{ trans('cards.editors.not_found') }}
                </p>
            @endif
        </div>
    </div>
@endcan

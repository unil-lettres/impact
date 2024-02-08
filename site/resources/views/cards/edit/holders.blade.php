<div class="card">
    <div class="card-header">
        {{ trans('cards.holders') }}
    </div>
    <div class="card-body">
        @can('parameters', $card)
            <p>{{ trans('cards.choose_holders') }}</p>

            @if ($users->isNotEmpty())
                <div class="col-12 mb-3">
                    <div id="rct-multi-holder-select"
                         data='{{ json_encode(['record' => $card, 'options' => $users, 'defaults' => $holders]) }}'
                    ></div>
                    <div class="form-text">{{ trans('cards.edit.holders_are_auto_save') }}</div>
                </div>
            @else
                <p class="text-secondary">
                    {{ trans('cards.users.not_found') }}
                </p>
            @endif
        @else
            <div>{{ $holders->isEmpty() ? '' : $holders->implode('name', ', ') }}</div>
        @endcan
    </div>
</div>

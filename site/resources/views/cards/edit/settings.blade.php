<div class="card">
    <div class="card-header">
        {{ trans('cards.global_settings') }}
    </div>
    <div class="card-body">
        <div class="col-12 mb-3 row">
            <label for="title" class="col-md-4 col-form-label">
                {{ trans('cards.title') }}
            </label>
            <div class="col-md-8">
                <input id="title"
                       type="text"
                       name="title"
                       value="{{ old('title', $card->title) }}"
                       class="form-control"
                >
            </div>
        </div>

        <div class="col-12 mb-3 row">
            <label for="state" class="col-md-4 form-label">
                {{ trans('cards.state') }}
            </label>
            <div class="col-md-8">
                @if(Helpers::isStateSelectEditable($card))
                    <input id="state" name="state" type="hidden" value="{{ $card->state ? $card->state->id : '' }}">
                    <div id="rct-single-state-select"
                         reference="state"
                         data='{{ json_encode(['options' => $states, 'default' => $card->state, 'clearable' => false]) }}'
                    ></div>
                @else
                    <div>{{ $card->state ? $card->state->name : '' }}</div>
                @endif
            </div>
        </div>

        <div class="col-12 mb-3 row">
            <label for="date" class="col-md-4 form-label">
                {{ trans('cards.date') }}
            </label>
            <div class="col-md-8">
                // Date input placeholder
            </div>
        </div>

        <div class="col-12 mb-3 row">
            <label for="tags" class="col-md-4 form-label">
                {{ trans('cards.tags') }}
            </label>
            <div class="col-md-8">
                // Tags input placeholder
            </div>
        </div>

        @can('parameters', $card)
            <div class="col-12 mb-3 row">
                <label for="no_emails" class="col-md-4 form-label">
                    {{ trans('cards.no_emails') }}
                    <i class="far fa-question-circle"
                       data-bs-toggle="tooltip"
                       data-placement="top"
                       title="{{ trans('cards.send_mails') }}">
                    </i>
                </label>
                <div class="col-md-8">
                    <div class="form-check">
                        <input id="no_emails"
                               type="checkbox"
                               name="no_emails"
                               {{ old('no_emails', $card->options['no_emails']) ? 'checked' : '' }}
                               class="form-check-input"
                        >
                    </div>
                </div>
            </div>
        @endcan
    </div>
</div>

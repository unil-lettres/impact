<div class="card">
    <div class="card-header">
        {{ trans('cards.global_settings') }}
    </div>
    <div class="card-body">
        <div class="form-group row">
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

        <div class="form-group row">
            <label for="state" class="col-md-4">
                {{ trans('cards.state') }}
            </label>
            <div class="col-md-8">
                // State input placeholder
            </div>
        </div>

        <div class="form-group row">
            <label for="date" class="col-md-4">
                {{ trans('cards.date') }}
            </label>
            <div class="col-md-8">
                // Date input placeholder
            </div>
        </div>

        <div class="form-group row">
            <label for="tags" class="col-md-4">
                {{ trans('cards.tags') }}
            </label>
            <div class="col-md-8">
                // Tags input placeholder
            </div>
        </div>

        <div class="form-group row">
            <label for="emails" class="col-md-4">
                {{ trans('cards.emails') }}
                <i class="far fa-question-circle"
                   data-toggle="tooltip"
                   data-placement="top"
                   title="{{ trans('cards.send_mails') }}">
                </i>
            </label>
            <div class="col-md-8">
                <div class="form-check">
                    <input id="emails"
                           type="checkbox"
                           name="emails"
                           {{ old('emails', $card->options['emails']) ? 'checked' : '' }}
                           class="form-check-input"
                    >
                </div>
            </div>
        </div>
    </div>
</div>

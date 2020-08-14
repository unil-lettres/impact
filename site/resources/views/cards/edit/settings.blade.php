<!-- TODO: translations -->
<div class="card">
    <div class="card-header">
        Global settings
    </div>
    <div class="card-body">
        <div class="form-group row">
            <label for="emails" class="col-md-4">
                Emails
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

        <div class="form-group row">
            <label for="state" class="col-md-4">
                État
            </label>
            <div class="col-md-8">
                // State input placeholder
            </div>
        </div>

        <div class="form-group row">
            <label for="date" class="col-md-4">
                Date
            </label>
            <div class="col-md-8">
                // Date input placeholder
            </div>
        </div>

        <div class="form-group row">
            <label for="tags" class="col-md-4">
                Étiquettes
            </label>
            <div class="col-md-8">
                // Tags input placeholder
            </div>
        </div>
    </div>
</div>

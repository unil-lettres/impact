<div class="col-12 mb-3 row">
    <label for="action-email-subject" class="col-md-3 col-form-label">
        {{ trans('states.email_subject') }}
    </label>
    <div class="col-md-9">
        <input id="action-email-subject"
               type="text"
               name="action-email-subject"
               value="{{ $activeState->getActionData(0, \App\Enums\ActionType::Email)['subject'] ?? trans('states.email_subject_default') }}"
               class="form-control"
        >
    </div>
</div>
<div class="col-12 mb-3 row">
    <label for="action-email-message" class="col-md-3 col-form-label">
        {{ trans('states.email_message') }}
        <i class="far fa-question-circle"
           data-bs-toggle="tooltip"
           data-placement="top"
           title="{{ trans('states.email_message_help') }}">
        </i>
    </label>
    <div class="col-md-9">
        <textarea class="form-control"
                  name="action-email-message"
                  id="action-email-message"
                  rows="3">{{ $activeState->getActionData(0, \App\Enums\ActionType::Email)['message'] ?? trans('states.email_message_default') }}</textarea>
    </div>
</div>

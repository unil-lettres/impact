@if(Helpers::boxIsEditable($card, $reference))
    <div class="card">
        <div class="card-header">
            {{ trans('cards.box') }} 4 ({{ $card->options[$reference]['title'] }})
        </div>
        <div class="card-body">
            @can('parameters', $card)
                <div class="col-12 mb-3 row">
                    <label for="box4-hidden" class="col-md-4 form-label">
                        {{ trans('cards.hide') }}
                    </label>
                    <div class="col-md-8">
                        <div class="form-check">
                            <input id="box4-hidden"
                                   type="checkbox"
                                   name="box4-hidden"
                                   {{ old('box4-hidden', $card->options['box4']['hidden']) ? 'checked' : '' }}
                                   class="form-check-input"
                            >
                        </div>
                    </div>
                </div>
            @endcan

            <div class="col-12 mb-3 row">
                <label for="box4-title" class="col-md-4 col-form-label">
                    {{ trans('cards.title') }}
                </label>
                <div class="col-md-8">
                    <input id="box4-title"
                           type="text"
                           name="box4-title"
                           value="{{ old('box4-title', $card->options['box4']['title']) }}"
                           class="form-control"
                    >
                </div>
            </div>
        </div>
    </div>
@endif

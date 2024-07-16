@if($card->boxIsEditable($reference))
    <div class="card">
        <div class="card-header">
            {{ trans('cards.box') }} 3 ({{ $card->options[$reference]['title'] }})
        </div>
        <div class="card-body">
            @can('parameters', $card)
                <div class="col-12 mb-3 row">
                    <label for="box3-hidden" class="col-md-4 form-label">
                        {{ trans('cards.hide') }}
                    </label>
                    <div class="col-md-8">
                        <div class="form-check">
                            <input id="box3-hidden"
                                   type="checkbox"
                                   name="box3-hidden"
                                   {{ old('box3-hidden', $card->options['box3']['hidden']) ? 'checked' : '' }}
                                   class="form-check-input"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-3 row">
                    <label for="box3-fixed" class="col-md-4 form-label">
                        {{ trans('cards.fixed_height') }}
                    </label>
                    <div class="col-md-8">
                        <div class="form-check">
                            <input id="box3-fixed"
                                   type="checkbox"
                                   name="box3-fixed"
                                   {{ old('box3-fixed', $card->options['box3']['fixed']) ? 'checked' : '' }}
                                   class="form-check-input"
                            >
                        </div>
                    </div>
                </div>
            @endcan

            <div class="col-12 mb-3 row">
                <label for="box3-title" class="col-md-4 col-form-label">
                    {{ trans('cards.title') }}
                </label>
                <div class="col-md-8">
                    <input id="box3-title"
                           type="text"
                           name="box3-title"
                           value="{{ old('box3-title', $card->options['box3']['title']) }}"
                           class="form-control"
                    >
                </div>
            </div>
        </div>
    </div>
@endif

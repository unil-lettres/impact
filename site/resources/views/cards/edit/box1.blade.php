@if(Helpers::boxIsEditable($card, $reference))
    <div class="card">
        <div class="card-header">
            {{ trans('cards.box') }} 1 ({{ trans('cards.source') }})
        </div>
        <div class="card-body">
            @can('parameters', $card)
                <div class="col-12 mb-3 row">
                    <label for="box1-hidden" class="col-md-4 form-label">
                        {{ trans('cards.hide') }}
                    </label>
                    <div class="col-md-8">
                        <div class="form-check">
                            <input id="box1-hidden"
                                   type="checkbox"
                                   name="box1-hidden"
                                   {{ old('box1-hidden', $card->options['box1']['hidden']) ? 'checked' : '' }}
                                   class="form-check-input"
                            >
                        </div>
                    </div>
                </div>
            @endcan

            <div class="col-12 mb-3 row">
                <label for="box1-file" class="col-md-4 col-form-label">
                    {{ trans('cards.select_source') }}
                </label>
                <div class="col-md-8">
                    <input id="box1-file" name="box1-file" type="hidden" value="{{ $card->file ? $card->file->id : '' }}">
                    <div id="rct-single-file-select"
                         reference="box1-file"
                         data='{{ json_encode(['options' => $files, 'default' => $card->file, 'clearable' => true]) }}'
                    ></div>
                </div>
            </div>

            <div class="col-12 mb-3 row">
                <label for="box1-link" class="col-md-4 col-form-label">
                    {{ trans('cards.external_link') }}
                </label>
                <div class="col-md-8">
                    <input id="box1-link"
                           type="url"
                           name="box1-link"
                           value="{{ old('box1-link', $card->options['box1']['link']) }}"
                           class="form-control"
                    >
                </div>
            </div>

            <div class="col-12 mb-3 row">
                <label class="col-md-4 col-form-label">
                    {{ trans('cards.extract') }}
                    <i class="far fa-question-circle"
                       data-bs-toggle="tooltip"
                       data-placement="top"
                       title="{{ trans('cards.extract_help') }}">
                    </i>
                </label>
                <div class="col-md-4">
                    <input id="box1-start"
                           type="text"
                           name="box1-start"
                           placeholder="{{ trans('cards.start') }}"
                           value="{{ old('box1-start', $card->options['box1']['start']) }}"
                           class="form-control"
                    >
                </div>
                <div class="col-md-4">
                    <input id="box1-end"
                           type="text"
                           name="box1-end"
                           placeholder="{{ trans('cards.end') }}"
                           value="{{ old('box1-end', $card->options['box1']['end']) }}"
                           class="form-control"
                    >
                </div>
            </div>
        </div>
    </div>
@endif

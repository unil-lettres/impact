<!-- TODO: translations -->
<div class="card">
    <div class="card-header">
        Case 1 ({{ trans('cards.source') }})
    </div>
    <div class="card-body">
        <div class="form-group row">
            <label for="box1-hidden" class="col-md-4">
                Cacher
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

        <div class="form-group row">
            <label for="box1-file" class="col-md-4 col-form-label">
                Sélection de la source
            </label>
            <div class="col-md-8">
                <input id="box1-file" name="box1-file" type="hidden" value="{{ $card->file ? $card->file->id : '' }}">
                <div id="rct-single-file-select"
                     reference="box1-file"
                     data='{{ json_encode(['options' => $files, 'default' => $card->file, 'clearable' => true]) }}'
                ></div>
            </div>
        </div>

        <div class="form-group row">
            <label for="box1-link" class="col-md-4 col-form-label">
                Lien externe
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
    </div>
</div>

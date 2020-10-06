<div class="card">
    <div class="card-header">
        {{ trans('cards.box') }} 2 ({{ trans('cards.transcription') }})
    </div>
    <div class="card-body">
        <div class="form-group row">
            <label for="box2-hidden" class="col-md-5">
                {{ trans('cards.hide') }}
            </label>
            <div class="col-md-7">
                <div class="form-check">
                    <input id="box2-hidden"
                           type="checkbox"
                           name="box2-hidden"
                           {{ old('box2-hidden', $card->options['box2']['hidden']) ? 'checked' : '' }}
                           class="form-check-input"
                    >
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="box2-sync" class="col-md-5">
                {{ trans('cards.sync_with_source') }}
            </label>
            <div class="col-md-7">
                <div class="form-check">
                    <input id="box2-sync"
                           type="checkbox"
                           name="box2-sync"
                           {{ old('box2-sync', $card->options['box2']['sync']) ? 'checked' : '' }}
                           class="form-check-input"
                    >
                </div>
            </div>
        </div>
    </div>
</div>

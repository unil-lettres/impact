<div class="card">
    <div class="card-header">
        {{ trans('cards.box') }} 2 ({{ trans('cards.transcription') }})
    </div>
    <div class="card-body">
        <div class="col-12 mb-3 row">
            <label for="box2-hidden" class="col-md-5 form-label">
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

        <div class="col-12 mb-3 row">
            <label for="box2-sync" class="col-md-5 form-label">
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

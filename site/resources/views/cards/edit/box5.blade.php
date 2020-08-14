<!-- TODO: translations -->
<div class="card">
    <div class="card-header">
        Case 5 (Documents)
    </div>
    <div class="card-body">
        <div class="form-group row">
            <label for="box5-hidden" class="col-md-4">
                Cacher
            </label>
            <div class="col-md-8">
                <div class="form-check">
                    <input id="box5-hidden"
                           type="checkbox"
                           name="box5-hidden"
                           {{ old('box5-hidden', $card->options['box5']['hidden']) ? 'checked' : '' }}
                           class="form-check-input"
                    >
                </div>
            </div>
        </div>
    </div>
</div>

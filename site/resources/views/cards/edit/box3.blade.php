<!-- TODO: translations -->
<div class="card">
    <div class="card-header">
        Case 3 ({{ $card->options[$reference]['title'] }})
    </div>
    <div class="card-body">
        <div class="form-group row">
            <label for="box3-hidden" class="col-md-4">
                Cacher
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

        <div class="form-group row">
            <label for="box3-title" class="col-md-4 col-form-label">
                Titre
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

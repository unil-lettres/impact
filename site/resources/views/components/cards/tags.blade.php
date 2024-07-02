<div class="flex-fill d-flex gap-1" x-data="{loading: false, tagsInline: '{{ $card->tags->isEmpty() ? '-' : addslashes($card->tags->implode('name', ', ')) }}', edition: false, canEdit: {{Auth::user()->can('update', $card) && Helpers::areCardSettingsEditable($card) ? 'true' : 'false'}}}">
    <div class="flex-fill">
        <div x-show.important="canEdit && edition" class="d-flex gap-1" x-cloak>
            <div>{{ trans('cards.tags') }}:</div>
            <div class="d-flex gap-1 flex-column">
                <div
                    id="rct-multi-tag-select"
                    createLabel="{{ trans('general.create_select_option_label') }}"
                    data='{{ json_encode(['record' => $card, 'options' => $card->course->tags, 'defaults' => $card->tags ]) }}'
                ></div>
                <div class="d-flex align-items-center gap-2">
                    <button
                        @click="edition = false; loading = true; axios.get(`/cards/{{$card->id}}/tagsInline`).then(response => {tagsInline = response.data.value; loading = false;})"
                        class="btn btn-primary float-right"
                    >
                        {{ trans('cards.complete') }}
                    </button>
                    <div class="form-text" style="margin-top: 0;">{{ trans('cards.edit.tags_are_auto_save') }}</div>
                </div>
            </div>
        </div>
        <div
            @click="edition = true"
            class="show-icon-on-hover cursor-pointer d-flex gap-1 align-items-top"
            x-show.important="!canEdit || !edition"
        >
            <div>{{ trans('cards.tags') }}:</div>
            <span x-text="tagsInline"></span>
            <div x-show="loading" x-cloak class="spinner-border text-primary spinner-border-sm fs-5 align-self-center"></div>
            <i class="fs-6 fa-solid fa-pen align-self-center"></i>
        </div>
    </div>
</div>

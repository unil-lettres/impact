<div class="media-status">
    @if(Helpers::cardHasSource($card))
        <div class="text-secondary text-center fs-5 lh-sm p-4">
            @if(Helpers::isFileStatus($card->file, \App\Enums\FileStatus::Ready))
                <div class="fa-2x">
                    <a href="#" onClick="window.location.reload()">
                        <i class="fa-solid fa-rotate-right text-secondary"></i>
                    </a>
                </div>

                {{ trans('messages.card.media.ready') }}
            @elseif(Helpers::isFileStatus($card->file, \App\Enums\FileStatus::Failed))
                <div class="fa-2x">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>

                {{ trans('messages.card.media.failed') }}
            @else
                <div class="progress mb-3 mt-2" style="height: 20px; width:50%; margin: auto;">
                    <div
                        class="progress-bar bg-secondary"
                        role="progressbar"
                        style="width: {{ $progress }}%"
                        aria-valuenow="{{ $progress }}"
                        aria-valuemin="0"
                        aria-valuemax="100">
                    </div>
                </div>

                {{ trans('messages.card.media.processing') }}
            @endif
        </div>
    @else
        <p class="text-secondary text-center p-4">
            {{ trans('messages.card.media.not.selected') }}
        </p>
    @endif
</div>

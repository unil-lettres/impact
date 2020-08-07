<!-- TODO: translations -->
<div class="card">
    <div class="card-header">
        <span class="font-weight-bolder">1. Source</span>

        @can('upload', [\App\File::class, $course, $card])
            <input id="card_id" name="card_id" type="hidden" value="{{ $card->id }}">
            <input id="course_id" name="course_id" type="hidden" value="{{ $course->id }}">

            <div id="rct-uploader" class="float-right"
                 data='{{ json_encode(['locale' => Helpers::currentLocal(), 'modal' => true, 'label' => 'Envoyer']) }}'
            ></div>
        @endcan
    </div>
    <div class="card-body p-0">
        @if($card->file)
            @if(Helpers::isFileReady($card->file))
                <div class="mt-2">
                    @if($card->file->type === 'video')
                        <video width="100%" height="100%" controls>
                            <source src="{{ Helpers::fileUrl($card->file->filename) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    @elseif($card->file->type === 'audio')
                        <audio controls>
                            <source src="{{ Helpers::fileUrl($card->file->filename) }}" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                    @endif
                </div>
            @elseif(Helpers::isFileFailed($card->file))
                <p class="text-danger text-center p-3">
                    The processing of the file failed, please try to send it again, or contact an administrator.
                </p>
            @else
                <p class="text-primary text-center p-3">
                    The media is processing, please wait.
                </p>
            @endif
        @else
            <p class="text-secondary text-center p-3">
                No media selected.
            </p>
        @endif
    </div>
</div>

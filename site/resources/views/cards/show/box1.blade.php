@if($card->boxIsVisible($reference))
    <div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hide-on-read-only' : '' }}">
        <div class="card-header">
            <div class="d-flex align-items-center">
                <span class="fw-bolder me-auto">1. {{ trans('cards.source') }}</span>
                <div class="hide-on-read-only">
                    <div class="d-flex gap-2">
                        @if($card->boxIsEditable($reference))
                            @can('download', [\App\File::class, $card->file])
                                <span>
                                    <a href="{{ route('files.download', ['file' => $card->file->id, 'card' => $card->id]) }}"
                                       class="btn btn-primary"
                                       title="{{ trans('files.download') }}">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                </span>
                            @endcan

                            @can('parameters', $card)
                                <livewire:toggle-box-visibility :card="$card" box="box1" />
                            @endcan

                            @can('upload', [\App\File::class, $course, $card])
                                <div id="rct-files"
                                    data='{{ json_encode(['locale' => Helpers::currentLocal(), 'label' => trans('files.upload'), 'filenameLabel' => trans('files.filename.label'), 'course_id' => $course->id, 'card_id' => $card->id]) }}'
                                ></div>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <livewire:media :card="$card" :reference="$reference"/>
        </div>
    </div>
@endif

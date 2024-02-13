@if($card->boxIsVisible($reference))
    <div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hidden' : '' }}">
        <div class="card-header">
            <div class="d-flex gap-2 align-items-center">
                <span class="fw-bolder me-auto">1. {{ trans('cards.source') }}</span>

                @if($card->boxIsEditable($reference))
                    @can('parameters', $card)
                        <livewire:toggle-box-visibility :card="$card" box="box1" />
                    @endcan

                    @can('upload', [\App\File::class, $course, $card])
                        <div id="rct-files"
                            data='{{ json_encode(['locale' => Helpers::currentLocal(), 'label' => trans('files.upload'), 'course_id' => $course->id, 'card_id' => $card->id]) }}'
                        ></div>
                    @endcan
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <livewire:media :card="$card" :reference="$reference"/>
        </div>
    </div>
@endif

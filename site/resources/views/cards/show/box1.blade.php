@if($card->boxIsVisible($reference))
    <div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hidden' : '' }}">
        <div class="card-header">
            <span class="fw-bolder">1. {{ trans('cards.source') }}</span>

            @if($card->boxIsEditable($reference))
                @can('upload', [\App\File::class, $course, $card])
                    <div id="rct-files" class="float-end"
                         data='{{ json_encode(['locale' => Helpers::currentLocal(), 'label' => trans('files.upload'), 'course_id' => $course->id, 'card_id' => $card->id]) }}'
                    ></div>
                @endcan
            @endif
        </div>
        <div class="card-body p-0">
            <livewire:media :card="$card" :reference="$reference"/>
        </div>
    </div>
@endif

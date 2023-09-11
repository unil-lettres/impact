@if($card->boxIsVisible($reference))
    <div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hidden' : '' }}">
        <div class="card-header">
            <span class="fw-bolder">5. {{ trans('cards.documents') }}</span>

            @can('upload', [\App\Policies\AttachmentPolicy::class, $course, $card])
                <!-- boxIsEditable check already included in the policy -->
                <div class="float-end">
                    <div id="rct-attachments" class="float-end"
                         data='{{ json_encode(['locale' => Helpers::currentLocal(), 'label' => trans('files.add'), 'maxNumberOfFiles' => 5, 'course_id' => $course->id, 'card_id' => $card->id]) }}'
                    ></div>
                </div>
            @endcan
        </div>
        <div class="card-body">
            <livewire:attachments :card="$card" :reference="$reference"/>
        </div>
    </div>
@endif

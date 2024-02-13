@if($card->boxIsVisible($reference))
    <div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hide-on-read-only' : '' }}">
        <div class="card-header">
            <div class="d-flex gap-2 align-items-center">
                <span class="fw-bolder me-auto">5. {{ trans('cards.documents') }}</span>
                <div class="hide-on-read-only">
                    <div class="d-flex gap-2">
                        @if($card->boxIsEditable($reference))
                            @can('parameters', $card)
                                <livewire:toggle-box-visibility :card="$card" box="box5" />
                            @endcan
                        @endif

                        @can('upload', [\App\Policies\AttachmentPolicy::class, $course, $card])
                            <!-- boxIsEditable check already included in the policy -->
                            <div class="float-end">
                                <div id="rct-attachments" class="float-end"
                                    data='{{ json_encode(['locale' => Helpers::currentLocal(), 'label' => trans('files.add'), 'maxNumberOfFiles' => 5, 'course_id' => $course->id, 'card_id' => $card->id]) }}'
                                ></div>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <livewire:attachments :card="$card" :reference="$reference"/>
        </div>
    </div>
@endif

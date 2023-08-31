@if($card->boxIsVisible($reference))
    <div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hidden' : '' }}">
        <div class="card-header">
            <span class="fw-bolder">5. {{ trans('cards.documents') }}</span>

            @if($card->boxIsEditable($reference))
                @can('upload', [\App\Policies\AttachmentPolicy::class, $course, $card])
                    <div class="float-end">
                        <div id="rct-attachments" class="float-end"
                             data='{{ json_encode(['locale' => Helpers::currentLocal(), 'label' => trans('files.add'), 'maxNumberOfFiles' => 5, 'course_id' => $course->id, 'card_id' => $card->id]) }}'
                        ></div>
                    </div>
                @endcan
            @endif
        </div>
        <div class="card-body">
            @if ($card->attachments->isNotEmpty())
                <ul>
                    @foreach ($card->attachments as $attachment)
                        @can('view', [\App\Policies\AttachmentPolicy::class, $attachment])
                            <li>
                                <!-- TODO: add logic to delete attachments -->
                                <a href="{{ Helpers::fileUrl($attachment->filename) }}"
                                   title="{{ trans('files.url') }}"
                                   target="_blank">
                                    {{ $attachment->name }}
                                </a>
                            </li>
                        @endcan
                    @endforeach
                </ul>
            @else
                <p class="text-secondary text-center">
                    {{ trans('messages.card.no.attachments') }}
                </p>
            @endif
        </div>
    </div>
@endif

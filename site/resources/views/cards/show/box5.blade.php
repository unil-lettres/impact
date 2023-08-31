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
            <div class="attachments-list list-group">
                @if ($card->attachments->isNotEmpty())
                    @foreach ($card->attachments as $attachment)
                        @can('view', [\App\Policies\AttachmentPolicy::class, $attachment])
                            <div>
                                <span class="align-middle">
                                    <a href="{{ Helpers::fileUrl($attachment->filename) }}"
                                       title="{{ trans('files.url') }}"
                                       target="_blank">
                                        {{ $attachment->name }}
                                    </a>
                                </span>
                                <span class="actions">
                                    @can('forceDelete', [\App\Policies\AttachmentPolicy::class, $attachment])
                                        <span class="float-end">
                                            <form class="with-delete-confirm" method="post"
                                                  action="{{ route('files.destroy', $attachment->id) }}">
                                                @method('DELETE')
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm btn-danger"
                                                        data-bs-toggle="tooltip"
                                                        data-placement="top"
                                                        title="{{ trans('files.delete') }}">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </span>
                                    @endcan
                                </span>
                            </div>
                        @endcan
                    @endforeach
                @else
                    <p class="text-secondary text-center">
                        {{ trans('messages.card.no.attachments') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
@endif

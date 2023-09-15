<div class="attachments-list list-group" {{ $card->boxIsEditable($reference) ? 'wire:poll' : '' }}>
    @if ($card->attachments->isNotEmpty())
        @foreach ($card->attachments as $attachment)
            @can('view', [\App\Policies\AttachmentPolicy::class, $attachment])
                <div class="attachment">
                    <span class="align-middle">
                        @if(Helpers::isFileStatus($attachment, \App\Enums\FileStatus::Ready))
                            <a href="{{ Helpers::fileUrl($attachment->filename) }}"
                               title="{{ trans('files.url') }}"
                               target="_blank">
                            {{ Str::limit($attachment->name, 40) }}
                            </a>
                        @elseif(Helpers::isFileStatus($attachment, \App\Enums\FileStatus::Failed))
                            {{ Str::limit($attachment->name, 40) }}
                            <span class="text-danger">({{ trans('messages.file.error') }})</span>
                        @else
                            {{ Str::limit($attachment->name, 40) }}
                        @endif
                    </span>

                    <span class="actions">
                        <span class="float-end">
                            @can('forceDelete', [\App\Policies\AttachmentPolicy::class, $attachment])
                                <button type="submit"
                                        class="btn btn-sm btn-danger"
                                        data-bs-toggle="tooltip"
                                        data-placement="top"
                                        title="{{ trans('files.delete') }}"
                                        wire:confirm="{{ trans('messages.confirm.delete') }}"
                                        wire:click="delete({{ $attachment->id }})">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            @endcan

                            @if(Helpers::isFileStatus($attachment, \App\Enums\FileStatus::Processing) || Helpers::isFileStatus($attachment, \App\Enums\FileStatus::Transcoding))
                                {!! Helpers::fileStatusBadge($attachment) !!}
                            @endif
                        </span>
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

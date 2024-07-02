<div
    wire:poll.keep-alive="checkConcurrentEditing(window.editors || [])"
    title="{{ trans('messages.card.editor.concurrent_edition') }}"
>
    @if($concurrentEditing)
        <i class="fa-solid fa-users pulse"></i>
    @endif
</div>

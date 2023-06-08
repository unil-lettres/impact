@if(Helpers::boxIsVisible($card, $reference))
    <div class="card {{ $reference }} {{ Helpers::isHidden($card, $reference) ? 'hidden' : '' }}">
        <div class="card-header">
            <span class="fw-bolder">5. {{ trans('cards.documents') }}</span>

            @if(Helpers::boxIsEditable($card, $reference))
                <div class="float-end">
                    <!-- Button trigger document upload -->
                </div>
            @endif
        </div>
        <div class="card-body">
            // Add attachments upload
        </div>
    </div>
@endif

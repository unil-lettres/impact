@unless ($cards->isEmpty())
    <ul>
        @foreach ($cards as $card)
            @can('view', $card)
                <li>
                    <a href="{{ route('cards.show', $card->id) }}">{{ $card->title }}</a>
                    @can('forceDelete', $card)
                        <form class="with-delete-confirm" method="post" style="display: inline;"
                              action="{{ route('cards.destroy', $card->id) }}">
                            @method('DELETE')
                            @csrf
                            <button type="submit"
                                    class="btn btn-link"
                                    style="color: red; padding: 0;">
                                ({{ trans('cards.delete') }})
                            </button>
                        </form>
                    @endcan
                    <div class="text-black-50">{{ $card->state?->name }}</div>
                </li>
            @endcan
        @endforeach
    </ul>
@else
    <p class="text-secondary">
        {{ trans('cards.not_found') }}
    </p>
@endunless

@unless ($folders->isEmpty())
    <ul>
        @foreach ($folders as $folder)
            <li>
                <a href="{{ route('folders.show', $folder->id) }}">[-]{{ $folder->title }}</a>
                @can('forceDelete', $folder)
                    <form class="with-delete-confirm" method="post" style="display: inline;"
                          action="{{ route('folders.destroy', $folder->id) }}">
                        @method('DELETE')
                        @csrf
                        <button type="submit"
                                class="btn btn-link"
                                style="color: red; padding: 0;">
                            ({{ trans('folders.delete') }})
                        </button>
                    </form>
                @endcan
            </li>
        @endforeach
    </ul>
@else
    <p class="text-secondary">
        {{ trans('folders.not_found') }}
    </p>
@endunless

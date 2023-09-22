@unless ($folders->isEmpty())
    <ul>
        @foreach ($folders as $folder)
            <li>
                <a href="{{ route('folders.show', $folder->id) }}">[-]{{ $folder->title }}</a>
            </li>
        @endforeach
    </ul>
@else
    <p class="text-secondary">
        {{ trans('folders.not_found') }}
    </p>
@endunless

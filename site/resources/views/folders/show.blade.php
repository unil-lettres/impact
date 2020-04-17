@extends('layouts.app-base')

@section('content')
    <div id="folder">
        @can('view', $folder)
            @section('title')
                {{ $folder->title }}
            @endsection
            <hr>
            <div>
                @unless ($children->isEmpty())
                    <ul>
                        @foreach ($children as $folder)
                            @can('view', $folder)
                                <li>
                                    <a href="{{ route('folders.show', $folder->id) }}">[-]{{ $folder->title }}</a>
                                </li>
                            @endcan
                        @endforeach
                    </ul>
                @else
                    <p class="text-secondary">
                        <!-- // TODO: add translation -->
                        Pas de dossier trouvé
                    </p>
                @endunless
            </div>
        @endcan
    </div>
@endsection

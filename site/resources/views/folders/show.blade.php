@extends('layouts.app-base')

@section('content')
    <div id="folder">
        @can('view', $folder)
            @section('title')
                {{ $folder->title }}

                @can('update', $folder)
                    <a href="{{ route('folders.edit', $folder->id) }}"
                       class="btn btn-primary float-right mr-1">
                        {{ trans('folders.edit') }}
                    </a>
                @endcan
            @endsection
            <hr>
            <div>
                @include('shared.folders')
                @include('shared.cards')
            </div>
        @endcan
    </div>
@endsection

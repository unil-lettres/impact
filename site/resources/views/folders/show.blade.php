@extends('layouts.app-base')

@section('title')
    {{ $folder->title }}
@endsection

@can('view', $folder)
    @can('update', $folder)
        @section('actions')
            <a href="{{ route('folders.edit', $folder->id) }}"
                class="btn btn-primary">
                {{ trans('folders.edit') }}
            </a>
        @endsection
    @endcan
    @section('content')
        <div id="folder">
            <div>
                @include('shared.folders')
                @include('shared.cards')
            </div>
        </div>
    @endsection
@endcan

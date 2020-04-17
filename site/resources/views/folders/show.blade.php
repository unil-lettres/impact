@extends('layouts.app-base')

@section('content')
    <div id="folder">
        @can('view', $folder)
            @section('title')
                {{ $folder->title }}
            @endsection
            <hr>
            <div>
                @include('shared.folders')
                @include('shared.cards')
            </div>
        @endcan
    </div>
@endsection

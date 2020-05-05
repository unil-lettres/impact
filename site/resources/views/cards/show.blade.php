@extends('layouts.app-base')

@section('content')
    <div id="card">
        @can('view', $card)
            @section('title')
                {{ $card->title }}

                @can('update', $card)
                    <a href="{{ route('cards.edit', $card->id) }}"
                       class="btn btn-primary float-right">
                        {{ trans('cards.configure') }}
                    </a>
                @endcan
            @endsection
            <hr>
            <div>
                <div id="rct-uploader"
                     data='{{ json_encode(['locale' => \App\Helpers\Helpers::currentLocal()]) }}'
                ></div>
            </div>
        @endcan
    </div>
@endsection
